<?php

use App\Support\DeviceAssetMapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('devices')) {
            return;
        }

        $now = now();

        foreach (DB::table('devices')->orderBy('id')->get() as $row) {
            $payload = DeviceAssetMapper::usesMasterIdColumns()
                ? DeviceAssetMapper::fromDeviceRow($row)
                : DeviceAssetMapper::fromDeviceRowForLegacySchema($row);
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

    public function down(): void
    {
        // Data migration is not reversed automatically.
    }
};
