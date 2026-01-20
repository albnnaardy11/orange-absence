<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        // Super Admin
        $admin = User::firstOrCreate([
            'email' => 'admin@orange.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // Secretary
        $secretary = User::firstOrCreate([
            'email' => 'secretary@orange.com',
        ], [
            'name' => 'Secretary',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (!$secretary->hasRole('secretary')) {
            $secretary->assignRole('secretary');
        }

        // Divisions
        $divisions = [
            1 => 'Game',
            2 => 'Web',
            3 => 'Esport FF',
            4 => 'Esport ML',
            5 => 'Cyber',
        ];
        
        $createdDivisions = [];
        foreach ($divisions as $id => $name) {
            $div = Division::updateOrCreate(['id' => $id], [
                'name' => $name,
                'description' => "$name Division"
            ]);
            $createdDivisions[$id] = $div;
            
            // Assign Secretary to all for demo
            $secretary->divisions()->syncWithoutDetaching([$div->id]);

            // Create Schedule for TODAY so code generation works immediately
            $today = Carbon::now()->format('l'); 
            Schedule::firstOrCreate([
                'division_id' => $div->id,
                'day' => $today,
            ], [
                'start_time' => '08:00',
                'end_time' => '20:00',
            ]);
        }

        $this->call(MemberSeeder::class);
    }
}
