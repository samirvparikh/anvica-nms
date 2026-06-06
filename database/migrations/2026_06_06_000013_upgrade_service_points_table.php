<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_points', function (Blueprint $table) {
            $table->string('name')->nullable()->after('service_id');
            $table->string('slug')->nullable()->after('name');
            $table->string('unit')->nullable()->after('method');
            $table->decimal('warning_threshold', 12, 4)->nullable()->after('unit');
            $table->decimal('critical_threshold', 12, 4)->nullable()->after('warning_threshold');
            $table->string('status')->default('Active')->after('critical_threshold');
        });

        DB::table('service_points')->orderBy('id')->each(function ($point) {
            DB::table('service_points')->where('id', $point->id)->update([
                'name' => $point->point,
                'slug' => Str::slug($point->point),
            ]);
        });

        Schema::table('service_points', function (Blueprint $table) {
            $table->dropColumn('point');
        });
    }

    public function down(): void
    {
        Schema::table('service_points', function (Blueprint $table) {
            $table->string('point')->nullable()->after('service_id');
        });

        DB::table('service_points')->orderBy('id')->each(function ($point) {
            DB::table('service_points')->where('id', $point->id)->update([
                'point' => $point->name,
            ]);
        });

        Schema::table('service_points', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug', 'unit', 'warning_threshold', 'critical_threshold', 'status']);
        });
    }
};
