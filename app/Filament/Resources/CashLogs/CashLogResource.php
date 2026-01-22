<?php

namespace App\Filament\Resources\CashLogs;

use App\Filament\Resources\CashLogs\Pages\CreateCashLog;
use App\Filament\Resources\CashLogs\Pages\EditCashLog;
use App\Filament\Resources\CashLogs\Pages\ListCashLogs;
use App\Filament\Resources\CashLogs\Schemas\CashLogForm;
use App\Filament\Resources\CashLogs\Tables\CashLogsTable;
use App\Models\CashLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CashLogResource extends Resource
{
    protected static ?string $model = CashLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Absence Management';

    public static function form(Schema $schema): Schema
    {
        return CashLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashLogs::route('/'),
            'create' => CreateCashLog::route('/create'),
            'edit' => EditCashLog::route('/{record}/edit'),
        ];
    }
}
