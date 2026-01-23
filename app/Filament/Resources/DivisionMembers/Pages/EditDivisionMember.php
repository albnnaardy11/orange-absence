<?php

namespace App\Filament\Resources\DivisionMembers\Pages;

use App\Filament\Resources\DivisionMembers\DivisionMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisionMember extends EditRecord
{
    protected static string $resource = DivisionMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}

