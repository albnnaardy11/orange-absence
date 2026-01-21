<?php

namespace App\Filament\Resources\SuspendedMemberResource\Pages;

use App\Filament\Resources\SuspendedMemberResource;
use Filament\Resources\Pages\ListRecords;

class ListSuspendedMembers extends ListRecords
{
    protected static string $resource = SuspendedMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
