<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define roles
        $roles = [
            ['name' => 'admin'],
            ['name' => 'voorraadbeheerder'],
            ['name' => 'vrijwilliger']
        ];

        // Create roles if they don't exist
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

    }
}