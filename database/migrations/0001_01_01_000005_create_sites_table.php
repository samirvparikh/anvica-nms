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
        Schema::create('sites', function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('name');
            $Blueprint->integer('up_devices')->default(0);
            $Blueprint->integer('total_devices')->default(0);
            $Blueprint->integer('x_pos')->default(0); // map x position percentage
            $Blueprint->integer('y_pos')->default(0); // map y position percentage
            $Blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
