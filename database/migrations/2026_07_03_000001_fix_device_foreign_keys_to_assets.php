<?php

use App\Support\Migration\RepairsDeviceAssetForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use RepairsDeviceAssetForeignKeys;

    public function up(): void
    {
        if (! Schema::hasTable('assets') || ! Schema::hasTable('device_metrics')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        $this->dropDeviceForeignKeysIfPresent();
        $this->ensureAssetsExistForReferencedDeviceIds();
        $this->ensureDeviceIdColumnsMatchAssets();
        $this->addDeviceForeignKeysToAssets();

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No-op
    }
};
