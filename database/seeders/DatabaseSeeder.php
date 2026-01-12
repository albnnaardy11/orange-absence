<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

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
        $divNames = ['Game', 'Web', 'Network'];
        $createdDivisions = [];
        foreach ($divNames as $name) {
            $div = Division::firstOrCreate(['name' => $name], ['description' => "$name Division"]);
            $createdDivisions[] = $div;
            
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

        // Member
        $member = User::firstOrCreate([
            'email' => 'member@orange.com',
        ], [
            'name' => 'Member User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (!$member->hasRole('member')) {
            $member->assignRole('member');
        }
        // Assign to Violin
        $member->divisions()->sync([$createdDivisions[0]->id]);
    }
}
