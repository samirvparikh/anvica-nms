<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alarms', function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('device_name');
            $Blueprint->string('message');
            $Blueprint->enum('severity', ['Critical', 'Warning'])->default('Warning');
            $Blueprint->enum('status', ['Open', 'Acknowledged'])->default('Open');
            $Blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
