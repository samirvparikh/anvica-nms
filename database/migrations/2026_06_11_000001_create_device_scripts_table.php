<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_scripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('target_ip');
            $table->string('snmp_community');
            $table->string('nms_url');
            $table->json('interface_indexes');
            $table->string('public_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_scripts');
    }
};
