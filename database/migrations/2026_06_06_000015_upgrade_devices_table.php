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
            $table->foreignId('service_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('service_id')->constrained('device_vendors')->nullOnDelete();
            $table->string('hostname')->nullable()->after('name');
            $table->string('api_url')->nullable()->after('location');
            $table->string('api_username')->nullable()->after('api_url');
            $table->text('api_password')->nullable()->after('api_username');
            $table->string('snmp_version', 10)->default('2c')->after('api_password');
            $table->unsignedSmallInteger('snmp_port')->default(161)->after('snmp_version');
            $table->string('snmp_community')->default('public')->after('snmp_port');
            $table->string('device_type')->nullable()->after('type');
            $table->timestamp('last_seen')->nullable()->after('status');
        });

        DB::table('devices')->update([
            'device_type' => DB::raw('type'),
        ]);
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropColumn([
                'service_id',
                'vendor_id',
                'hostname',
                'api_url',
                'api_username',
                'api_password',
                'snmp_version',
                'snmp_port',
                'snmp_community',
                'device_type',
                'last_seen',
            ]);
        });
    }
};
