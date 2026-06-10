<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_point_codes', function (Blueprint $table) {
            $table->foreignId('service_point_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('service_points')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_point_codes', function (Blueprint $table) {
            $table->dropForeign(['service_point_id']);
            $table->dropColumn('service_point_id');
        });
    }
};
