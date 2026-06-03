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
        Schema::create('api_request_logs', function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('url');
            $Blueprint->string('method');
            $Blueprint->string('ip_address');
            $Blueprint->text('user_agent')->nullable();
            $Blueprint->text('referer')->nullable();
            $Blueprint->json('request_data')->nullable();
            $Blueprint->json('headers')->nullable();
            $Blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
