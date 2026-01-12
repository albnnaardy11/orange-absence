<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $secretary = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        
        // Define key permissions (optional but good practice)
        Permission::firstOrCreate(['name' => 'view_admin_panel']);
        
        $superAdmin->givePermissionTo(Permission::all());
        $secretary->givePermissionTo('view_admin_panel');
        // Members don't get admin panel access
        
        // Output for verification
        $this->command->info('Roles created: super_admin, secretary, member');
    }
}
