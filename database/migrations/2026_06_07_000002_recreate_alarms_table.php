<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
