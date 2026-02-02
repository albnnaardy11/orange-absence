<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
