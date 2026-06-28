<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_interfaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('assets')->cascadeOnDelete();
            $table->string('interface_name');
            $table->string('status')->default('up');
            $table->unsignedBigInteger('rx')->default(0);
            $table->unsignedBigInteger('tx')->default(0);
            $table->unsignedBigInteger('rx_packets')->default(0);
            $table->unsignedBigInteger('tx_packets')->default(0);
            $table->timestamps();

            $table->unique(['device_id', 'interface_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_interfaces');
    }
};
