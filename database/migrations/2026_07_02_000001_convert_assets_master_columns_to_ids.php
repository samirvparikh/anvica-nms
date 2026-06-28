<?php

use App\Services\ApplicationMasterService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> new_column => old_column */
    protected array $columnRenames = [
        'asset_type_id' => 'asset_type',
        'asset_category_id' => 'asset_category',
        'status_id' => 'status',
        'asset_group_id' => 'asset_group',
        'criticality_id' => 'criticality',
        'availability_requirement_id' => 'availability_requirement',
        'manufacturer_id' => 'manufacturer',
        'snmp_version_id' => 'snmp_version',
        'region_id' => 'region',
        'state_id' => 'state',
        'city_id' => 'city',
        'site_location_id' => 'site_location',
        'rack_id' => 'rack',
        'rack_unit_id' => 'rack_unit',
        'zone_id' => 'zone',
        'warranty_status_id' => 'warranty_status',
        'amc_status_id' => 'amc_status',
        'sla_policy_id' => 'sla_policy',
        'service_name_id' => 'service_name',
        'business_unit_id' => 'business_unit',
        'sla_availability_id' => 'sla_availability',
        'response_sla_id' => 'response_sla',
        'resolution_sla_id' => 'resolution_sla',
        'escalation_sla_id' => 'escalation_sla',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        $masterService = app(ApplicationMasterService::class);
        $fieldMap = config('application_master_fields.assets', []);

        Schema::table('assets', function (Blueprint $table) use ($fieldMap) {
            foreach (array_keys($fieldMap) as $column) {
                if (! Schema::hasColumn('assets', $column)) {
                    $table->unsignedBigInteger($column)->nullable();
                }
            }
        });

        foreach ($this->columnRenames as $newColumn => $oldColumn) {
            if (! Schema::hasColumn('assets', $oldColumn)) {
                continue;
            }

            $masterType = $fieldMap[$newColumn] ?? null;
            if (! $masterType) {
                continue;
            }

            DB::table('assets')
                ->select('id', $oldColumn)
                ->orderBy('id')
                ->each(function ($row) use ($newColumn, $oldColumn, $masterType, $masterService) {
                    if ($row->{$oldColumn} === null || $row->{$oldColumn} === '') {
                        return;
                    }

                    $masterId = $masterService->resolveId($masterType, $row->{$oldColumn});

                    if ($masterId) {
                        DB::table('assets')->where('id', $row->id)->update([$newColumn => $masterId]);
                    }
                });
        }

        Schema::disableForeignKeyConstraints();

        Schema::table('assets', function (Blueprint $table) use ($fieldMap) {
            foreach (array_keys($fieldMap) as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->foreign($column)->references('id')->on('application_masters')->nullOnDelete();
                }
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            foreach ($this->columnRenames as $newColumn => $oldColumn) {
                if (Schema::hasColumn('assets', $oldColumn) && Schema::hasColumn('assets', $newColumn)) {
                    $table->dropColumn($oldColumn);
                }
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally not reversed — restore from backup if needed.
    }
};
