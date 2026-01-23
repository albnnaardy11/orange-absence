<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DebtorsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Daftar Tunggakan Anggota';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::role('member')
                    ->whereHas('cashLogs', fn ($query) => $query->overdue())
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('debt_amount')
                    ->label('Total Tunggakan')
                    ->getStateUsing(function ($record) {
                        $debt = $record->cashLogs()->overdue()->sum('amount');
                        return 'Rp ' . number_format($debt, 0, ',', '.');
                    })
                    ->badge()
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}

