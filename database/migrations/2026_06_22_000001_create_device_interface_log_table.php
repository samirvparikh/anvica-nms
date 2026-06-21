<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_interface_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('interface_name');
            $table->string('if_index', 50)->nullable();
            $table->string('status')->default('Up');
            $table->unsignedBigInteger('rx')->default(0);
            $table->unsignedBigInteger('tx')->default(0);
            $table->unsignedBigInteger('rx_packets')->default(0);
            $table->unsignedBigInteger('tx_packets')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_id', 'interface_name', 'recorded_at']);
            $table->index(['device_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_interface_log');
    }
};
