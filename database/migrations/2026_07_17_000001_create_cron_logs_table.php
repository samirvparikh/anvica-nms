<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cron_logs')) {
            return;
        }

        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->text('message')->nullable();
            $table->integer('affected')->default(0);
            $table->integer('exit_code')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['command', 'started_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
