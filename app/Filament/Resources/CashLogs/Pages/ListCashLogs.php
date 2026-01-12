<?php

namespace App\Filament\Resources\CashLogs\Pages;

use App\Filament\Resources\CashLogs\CashLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashLogs extends ListRecords
{
    protected static string $resource = CashLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
