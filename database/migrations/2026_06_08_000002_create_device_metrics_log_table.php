<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_metrics_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('metric_slug');
            $table->decimal('metric_value', 16, 4);
            $table->string('metric_text')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_id', 'metric_slug', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_metrics_log');
    }
};
