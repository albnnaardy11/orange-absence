<?php

namespace App\Filament\Resources\SuspendedMembers\Pages;

use App\Filament\Resources\SuspendedMembers\SuspendedMemberResource;
use Filament\Resources\Pages\ListRecords;

class ListSuspendedMembers extends ListRecords
{
    protected static string $resource = SuspendedMemberResource::class;
}

