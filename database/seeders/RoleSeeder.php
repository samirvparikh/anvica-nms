<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => Role::SLUG_SUPERADMIN,
                'is_superadmin' => true,
                'is_admin' => true,
                'is_staff' => false,
                'assignable_by' => [],
                'sort_order' => 1,
            ],
            [
                'name' => 'Admin',
                'slug' => Role::SLUG_ADMIN,
                'is_superadmin' => false,
                'is_admin' => true,
                'is_staff' => false,
                'assignable_by' => [Role::SLUG_SUPERADMIN],
                'sort_order' => 2,
            ],
            [
                'name' => 'Manager',
                'slug' => Role::SLUG_MANAGER,
                'is_superadmin' => false,
                'is_admin' => false,
                'is_staff' => true,
                'assignable_by' => [Role::SLUG_SUPERADMIN, Role::SLUG_ADMIN],
                'sort_order' => 3,
            ],
            [
                'name' => 'Engineer',
                'slug' => Role::SLUG_ENGINEER,
                'is_superadmin' => false,
                'is_admin' => false,
                'is_staff' => true,
                'assignable_by' => [Role::SLUG_SUPERADMIN, Role::SLUG_ADMIN],
                'sort_order' => 4,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
