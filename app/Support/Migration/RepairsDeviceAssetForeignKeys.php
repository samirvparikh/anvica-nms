<?php

namespace App\Support\Migration;

use App\Support\DeviceAssetMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait RepairsDeviceAssetForeignKeys
{
    /**
     * @return array<int, array{table: string, column: string, onDelete: string, nullable?: bool}>
     */
    protected function deviceAssetForeignKeyDefinitions(): array
    {
        return [
            ['table' => 'tickets', 'column' => 'device_id', 'onDelete' => 'set null', 'nullable' => true],
            ['table' => 'maintenance_windows', 'column' => 'primary_device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_interface_log', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_downtime_events', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_scripts', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_metrics_log', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'alerts', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_interfaces', 'column' => 'device_id', 'onDelete' => 'cascade'],
            ['table' => 'device_metrics', 'column' => 'device_id', 'onDelete' => 'cascade'],
        ];
    }

    protected function dropDeviceForeignKeysIfPresent(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->deviceAssetForeignKeyDefinitions() as $definition) {
            if (! Schema::hasTable($definition['table']) || ! Schema::hasColumn($definition['table'], $definition['column'])) {
                continue;
            }

            try {
                Schema::table($definition['table'], function (Blueprint $table) use ($definition) {
                    $table->dropForeign([$definition['column']]);
                });
            } catch (\Throwable) {
                // Constraint may already be removed or use a legacy name.
            }
        }
    }

    protected function ensureDeviceIdColumnsMatchAssets(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('assets')) {
            return;
        }

        foreach ($this->deviceAssetForeignKeyDefinitions() as $definition) {
            if (! Schema::hasTable($definition['table']) || ! Schema::hasColumn($definition['table'], $definition['column'])) {
                continue;
            }

            $nullable = ($definition['nullable'] ?? false) ? 'NULL' : 'NOT NULL';

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` BIGINT UNSIGNED %s',
                $definition['table'],
                $definition['column'],
                $nullable
            ));
        }
    }

    protected function ensureAssetsExistForReferencedDeviceIds(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        $referencedIds = $this->collectReferencedDeviceIds();
        $existingIds = DB::table('assets')->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $missingIds = array_values(array_diff($referencedIds, $existingIds));

        if ($missingIds === []) {
            return;
        }

        $deviceRows = Schema::hasTable('devices')
            ? DB::table('devices')->whereIn('id', $missingIds)->get()->keyBy('id')
            : collect();

        foreach ($missingIds as $deviceId) {
            $deviceRow = $deviceRows->get($deviceId);

            if ($deviceRow) {
                $payload = $this->assetPayloadForMigration($deviceRow);
                $payload['id'] = $deviceId;
                $payload['created_at'] = $deviceRow->created_at ?? now();
                $payload['updated_at'] = $deviceRow->updated_at ?? now();
            } else {
                $payload = $this->buildStubAssetPayload($deviceId);
            }

            DB::table('assets')->insert($payload);
        }
    }

    /**
     * @return array<int, int>
     */
    protected function collectReferencedDeviceIds(): array
    {
        $ids = [];

        foreach ($this->deviceAssetForeignKeyDefinitions() as $definition) {
            if (! Schema::hasTable($definition['table']) || ! Schema::hasColumn($definition['table'], $definition['column'])) {
                continue;
            }

            $columnIds = DB::table($definition['table'])
                ->whereNotNull($definition['column'])
                ->distinct()
                ->pluck($definition['column'])
                ->map(static fn ($id) => (int) $id)
                ->all();

            $ids = array_merge($ids, $columnIds);
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<string, mixed>
     */
    protected function assetPayloadForMigration(object $row): array
    {
        if (Schema::hasColumn('assets', 'asset_type_id')) {
            return DeviceAssetMapper::fromDeviceRow($row);
        }

        return DeviceAssetMapper::fromDeviceRowForLegacySchema($row);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildStubAssetPayload(int $deviceId): array
    {
        $customerId = DB::table('users')->orderBy('id')->value('id');

        if (! $customerId) {
            throw new \RuntimeException('Cannot create asset stub for device '.$deviceId.' because no users exist.');
        }

        $now = now();
        $base = [
            'id' => $deviceId,
            'asset_name' => 'Migrated Device '.$deviceId,
            'model_number' => 'Generic',
            'serial_number' => 'MIG-'.$deviceId.'-'.Str::upper(Str::random(8)),
            'management_ip' => '127.0.0.1',
            'customer_id' => $customerId,
            'asset_id_auto' => sprintf('AST-MIG-%s-%04d', date('Y'), $deviceId),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('assets', 'asset_type_id')) {
            return array_merge($base, [
                'asset_type_id' => DeviceAssetMapper::resolveMasterId('asset_type', 'Router'),
                'asset_category_id' => DeviceAssetMapper::resolveMasterId('asset_category', 'Network Infrastructure'),
                'status_id' => DeviceAssetMapper::resolveMasterId('asset_status', 'Active'),
                'criticality_id' => DeviceAssetMapper::resolveMasterId('criticality', 'Medium'),
                'manufacturer_id' => DeviceAssetMapper::resolveMasterId('manufacturer', 'Cisco'),
            ]);
        }

        return array_merge($base, [
            'asset_type' => 'Router',
            'asset_category' => 'Network Infrastructure',
            'status' => 'Active',
            'criticality' => 'Medium',
            'manufacturer' => 'Cisco',
        ]);
    }

    protected function addDeviceForeignKeysToAssets(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('assets')) {
            return;
        }

        foreach ($this->deviceAssetForeignKeyDefinitions() as $definition) {
            if (! Schema::hasTable($definition['table']) || ! Schema::hasColumn($definition['table'], $definition['column'])) {
                continue;
            }

            if ($this->foreignKeyReferencesAssets($definition['table'], $definition['column'])) {
                continue;
            }

            Schema::table($definition['table'], function (Blueprint $table) use ($definition) {
                $foreign = $table->foreign($definition['column'])
                    ->references('id')
                    ->on('assets');

                if ($definition['onDelete'] === 'set null') {
                    $foreign->nullOnDelete();
                } else {
                    $foreign->cascadeOnDelete();
                }
            });
        }
    }

    protected function foreignKeyReferencesAssets(string $table, string $column): bool
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return false;
        }

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            [$table, $column, 'assets']
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
}
