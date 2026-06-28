<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60);
            $table->string('name', 191);
            $table->string('value', 190);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('Active');
            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->index(['type', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_masters');
    }
};
