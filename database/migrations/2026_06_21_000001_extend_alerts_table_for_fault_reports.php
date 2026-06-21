<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('alarm_type', 100)->nullable()->after('service_point_id');
            $table->timestamp('started_at')->nullable()->after('alarm_type');
            $table->timestamp('resolved_at')->nullable()->after('started_at');
            $table->unsignedInteger('duration_seconds')->nullable()->after('resolved_at');
            $table->timestamp('acknowledged_at')->nullable()->after('duration_seconds');
            $table->foreignId('acknowledged_by')->nullable()->after('acknowledged_at')->constrained('users')->nullOnDelete();
        });

        DB::statement("ALTER TABLE alerts MODIFY severity VARCHAR(20) NOT NULL DEFAULT 'warning'");

        DB::table('alerts')->whereNull('started_at')->update([
            'started_at' => DB::raw('created_at'),
        ]);

        DB::table('alerts')->where('status', 'closed')->whereNull('resolved_at')->update([
            'resolved_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropForeign(['acknowledged_by']);
            $table->dropColumn([
                'alarm_type',
                'started_at',
                'resolved_at',
                'duration_seconds',
                'acknowledged_at',
                'acknowledged_by',
            ]);
        });
    }
};
