<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DivisionMembersImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected int $divisionId;

    public function __construct(int $divisionId)
    {
        $this->divisionId = $divisionId;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function model(array $row)
    {
        // $row is now an associative array keyed by header name (lowercase)
        $email = $row['email'] ?? null;
        $name = $row['nama'] ?? null;

        if (!$email || !str_contains($email, '@')) {
            return null; 
        }

        // Clean up inputs
        $email = trim($email);
        $name = trim($name ?? explode('@', $email)[0]);

        // Validate email is unique (although updateOrCreate handles logic, 
        // we might want to avoid updating if existing user has different role?)
        // Standard updateOrCreate is fine.

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => $name,
            'password' => Hash::make('ORENSSOLUTION2026'),
            'email_verified_at' => now(),
        ]);

        if (!$user->hasRole('member')) {
            $user->assignRole('member');
        }

        // Sync Division
        $user->divisions()->syncWithoutDetaching([$this->divisionId]);

        return $user;
    }
}
