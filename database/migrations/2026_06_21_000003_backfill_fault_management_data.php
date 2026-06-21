<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('alerts', 'alarm_type')) {
            return;
        }

        $rows = DB::table('alerts')->whereNull('alarm_type')->get(['id', 'message']);

        foreach ($rows as $row) {
            $message = strtolower($row->message ?? '');

            $type = match (true) {
                str_contains($message, 'offline') || str_contains($message, 'device down') => 'Device Down',
                str_contains($message, 'cpu') => 'High CPU',
                str_contains($message, 'ram') => 'High RAM',
                str_contains($message, 'disk') => 'Disk Usage',
                str_contains($message, 'temperature') || str_contains($message, 'temp') => 'Temperature',
                str_contains($message, 'interface') => 'Interface Down',
                default => 'Threshold Violation',
            };

            DB::table('alerts')->where('id', $row->id)->update(['alarm_type' => $type]);
        }

        $downDevices = DB::table('devices')
            ->where('health_status', 'Down')
            ->pluck('id');

        foreach ($downDevices as $deviceId) {
            $hasOpenEvent = DB::table('device_downtime_events')
                ->where('device_id', $deviceId)
                ->whereNull('up_at')
                ->exists();

            if ($hasOpenEvent) {
                continue;
            }

            DB::table('device_downtime_events')->insert([
                'device_id' => $deviceId,
                'down_at' => now(),
                'reason' => 'Device Not Responding',
                'source' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data backfill only.
    }
};
