<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alerts') && ! Schema::hasColumn('alerts', 'converted_to_alarm_at')) {
            Schema::table('alerts', function (Blueprint $table) {
                $table->timestamp('converted_to_alarm_at')->nullable()->after('acknowledged_by');
            });
        }

        if (Schema::hasTable('alarms') && ! Schema::hasColumn('alarms', 'alert_id')) {
            Schema::table('alarms', function (Blueprint $table) {
                $table->unsignedBigInteger('alert_id')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('alerts') && Schema::hasColumn('alerts', 'converted_to_alarm_at')) {
            Schema::table('alerts', function (Blueprint $table) {
                $table->dropColumn('converted_to_alarm_at');
            });
        }

        if (Schema::hasTable('alarms') && Schema::hasColumn('alarms', 'alert_id')) {
            Schema::table('alarms', function (Blueprint $table) {
                $table->dropColumn('alert_id');
            });
        }
    }
};
