<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UsersImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'GAME'       => new DivisionMembersImport(1),
            'WEB'        => new DivisionMembersImport(2),
            'CYBER'      => new DivisionMembersImport(5),
            'E-SPORT FF' => new DivisionMembersImport(3),
            'E-SPORT ML' => new DivisionMembersImport(4),
        ];
    }
}

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
