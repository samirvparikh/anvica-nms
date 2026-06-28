<?php

use App\Support\Migration\RepairsDeviceAssetForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use RepairsDeviceAssetForeignKeys;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->dropDeviceForeignKeysIfPresent();

        // Add monitoring columns to assets before copying legacy device rows.
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            }
            if (! Schema::hasColumn('assets', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->constrained('device_vendors')->onDelete('set null');
            }
            if (! Schema::hasColumn('assets', 'api_url')) {
                $table->string('api_url')->nullable();
            }
            if (! Schema::hasColumn('assets', 'api_username')) {
                $table->string('api_username')->nullable();
            }
            if (! Schema::hasColumn('assets', 'api_password')) {
                $table->text('api_password')->nullable();
            }
            if (! Schema::hasColumn('assets', 'snmp_port')) {
                $table->integer('snmp_port')->default(161);
            }
            if (! Schema::hasColumn('assets', 'health_status')) {
                $table->string('health_status')->default('Up');
            }
            if (! Schema::hasColumn('assets', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }
        });

        if (Schema::hasTable('devices') && Schema::hasTable('assets')) {
            $now = now();

            foreach (DB::table('devices')->orderBy('id')->get() as $row) {
                $payload = $this->assetPayloadForMigration($row);
                $payload['updated_at'] = $now;

                if (DB::table('assets')->where('id', $row->id)->exists()) {
                    DB::table('assets')->where('id', $row->id)->update($payload);

                    continue;
                }

                DB::table('assets')->insert(array_merge($payload, [
                    'id' => $row->id,
                    'created_at' => $row->created_at ?? $now,
                    'updated_at' => $row->updated_at ?? $now,
                ]));
            }
        }

        $this->ensureAssetsExistForReferencedDeviceIds();

        Schema::dropIfExists('devices');

        $this->ensureDeviceIdColumnsMatchAssets();
        $this->addDeviceForeignKeysToAssets();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for down direction
    }
};
