<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alarms')) {
            return;
        }

        $now = now();

        foreach (DB::table('alarms')->orderBy('id')->get() as $alarm) {
            $device = DB::table('devices')
                ->where('name', $alarm->device_name)
                ->orderBy('id')
                ->first();

            if (! $device) {
                continue;
            }

            $severity = strtolower((string) $alarm->severity);
            if (! in_array($severity, ['critical', 'warning', 'info'], true)) {
                $severity = 'warning';
            }

            $isAcknowledged = strtolower((string) $alarm->status) === 'acknowledged';

            DB::table('alerts')->insert([
                'device_id' => $device->id,
                'service_point_id' => null,
                'alarm_type' => 'Legacy Alarm',
                'severity' => $severity,
                'message' => $alarm->message,
                'status' => $isAcknowledged ? 'closed' : 'open',
                'started_at' => $alarm->created_at,
                'resolved_at' => $isAcknowledged ? ($alarm->updated_at ?? $now) : null,
                'duration_seconds' => null,
                'acknowledged_at' => $isAcknowledged ? ($alarm->updated_at ?? $now) : null,
                'acknowledged_by' => null,
                'created_at' => $alarm->created_at ?? $now,
                'updated_at' => $alarm->updated_at ?? $now,
            ]);
        }

        Schema::dropIfExists('alarms');
    }

    public function down(): void
    {
        if (Schema::hasTable('alarms')) {
            return;
        }

        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('message');
            $table->enum('severity', ['Critical', 'Warning'])->default('Warning');
            $table->enum('status', ['Open', 'Acknowledged'])->default('Open');
            $table->timestamps();
        });
    }
};
