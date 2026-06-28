<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\RoleSeeder())->run();

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('mobile')->constrained('roles')->nullOnDelete();
        });

        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug');
        $legacyMap = [
            'superadmin' => 'superadmin',
            'admin' => 'admin',
            'manager' => 'manager',
            'engineer' => 'engineer',
            'user' => 'engineer',
        ];

        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            $slug = $legacyMap[$user->role] ?? 'engineer';
            $roleId = $roleIdsBySlug[$slug] ?? $roleIdsBySlug['engineer'] ?? null;

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer')->after('mobile');
        });

        $slugsByRoleId = DB::table('roles')->pluck('slug', 'id');

        foreach (DB::table('users')->select('id', 'role_id')->get() as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'role' => $slugsByRoleId[$user->role_id] ?? 'engineer',
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
