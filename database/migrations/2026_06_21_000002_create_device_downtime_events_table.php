<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_downtime_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('assets')->cascadeOnDelete();
            $table->timestamp('down_at');
            $table->timestamp('up_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('reason')->nullable();
            $table->string('source', 20)->default('poll');
            $table->timestamps();

            $table->index(['device_id', 'down_at']);
            $table->index(['device_id', 'up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_downtime_events');
    }
};
