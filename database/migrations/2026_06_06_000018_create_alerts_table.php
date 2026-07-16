<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->foreignId('service_point_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('severity', ['critical', 'warning', 'info'])->default('warning');
            $table->text('message');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
