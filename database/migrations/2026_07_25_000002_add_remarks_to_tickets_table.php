<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tickets')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'remarks')) {
                $table->text('remarks')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'remarks')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }
};
