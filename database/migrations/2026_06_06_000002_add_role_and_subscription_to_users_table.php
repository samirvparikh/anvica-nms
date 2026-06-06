<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->after('mobile');
            $table->unsignedInteger('device_limit')->nullable()->after('role');
            $table->date('start_date')->nullable()->after('device_limit');
            $table->date('expire_date')->nullable()->after('start_date');
            $table->foreignId('created_by')->nullable()->after('expire_date')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['role', 'device_limit', 'start_date', 'expire_date', 'created_by']);
        });
    }
};
