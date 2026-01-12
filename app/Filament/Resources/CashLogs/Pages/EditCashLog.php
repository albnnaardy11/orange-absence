<?php

namespace App\Filament\Resources\CashLogs\Pages;

use App\Filament\Resources\CashLogs\CashLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashLog extends EditRecord
{
    protected static string $resource = CashLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
