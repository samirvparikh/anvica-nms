<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('health_status', ['Up', 'Warning', 'Down'])->default('Up')->after('snmp_community');
        });

        DB::table('devices')->orderBy('id')->each(function ($device) {
            $health = in_array($device->status, ['Up', 'Warning', 'Down'], true)
                ? $device->status
                : 'Up';

            DB::table('devices')->where('id', $device->id)->update([
                'health_status' => $health,
            ]);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });

        DB::table('devices')->update([
            'status' => DB::raw("CASE WHEN health_status = 'Down' THEN 'inactive' ELSE 'active' END"),
        ]);

        Schema::table('devices', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('status')->default('Up')->change();
        });

        DB::table('devices')->update([
            'status' => DB::raw('health_status'),
        ]);

        DB::table('devices')->where('status', 'active')->update(['status' => 'Up']);
        DB::table('devices')->where('status', 'inactive')->update(['status' => 'Down']);

        Schema::table('devices', function (Blueprint $table) {
            $table->enum('status', ['Up', 'Warning', 'Down'])->default('Up')->change();
            $table->dropColumn('health_status');
        });
    }
};
