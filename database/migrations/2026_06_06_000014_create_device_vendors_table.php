<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->unique(['service_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_vendors');
    }
};
