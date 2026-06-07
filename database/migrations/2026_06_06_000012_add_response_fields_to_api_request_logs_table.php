<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('response_status')->nullable()->after('headers');
            $table->boolean('route_exists')->default(false)->after('response_status');
        });
    }

    public function down(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->dropColumn(['response_status', 'route_exists']);
        });
    }
};
