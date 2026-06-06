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
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('icon')->nullable()->after('slug');
        });

        DB::table('services')->orderBy('id')->each(function ($service) {
            DB::table('services')->where('id', $service->id)->update([
                'slug' => Str::slug($service->name),
            ]);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['slug', 'icon']);
        });
    }
};
