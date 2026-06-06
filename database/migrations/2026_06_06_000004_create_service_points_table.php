<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('point');
            $table->enum('method', ['SNMP', 'API', 'METHOD']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_points');
    }
};
