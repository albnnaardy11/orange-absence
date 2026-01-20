<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Spatie\Permission\Models\Role;

// Force load new class if autoloader is stuck
require_once __DIR__ . '/../../app/Imports/UsersImport.php';

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure member role exists
        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        // Members import from Excel
        $excelFile = base_path('EMAIL ANAK ORENS.xlsx');
        if (file_exists($excelFile)) {
            // UsersImport now handles multiple sheets logic internally
            Excel::import(new UsersImport, $excelFile);
            $this->command->info('Members imported from Excel per division.');
        } else {
            $this->command->warn('Excel file not found: ' . $excelFile);
        }

        // Manual Demo Member (optional, keeping for safety)
        // Ensure email is unique by using firstOrCreate
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
        
        // Default to Game division (id 1) if divisions exist
        if (\App\Models\Division::find(1)) {
            $member->divisions()->syncWithoutDetaching([1]);
        }

        // New Requirement: Check members without divisions and put them into CYBER (ID 5)
        $usersWithoutDivision = User::doesntHave('divisions')->role('member')->get();
        if ($usersWithoutDivision->count() > 0) {
            $cyberDivision = \App\Models\Division::find(5);
            if ($cyberDivision) {
                foreach ($usersWithoutDivision as $user) {
                    $user->divisions()->syncWithoutDetaching([5]);
                }
                $this->command->info("Assigned " . $usersWithoutDivision->count() . " members to Cyber division.");
            }
        }
    }
}
