<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->color('info'),
                TextColumn::make('financial_status')
                    ->label('Status Keuangan')
                    ->getStateUsing(function ($record) {
                        $count = $record->cashLogs()
                            ->where('status', 'unpaid')
                            ->get()
                            ->filter(fn ($log) => $log->is_overdue)
                            ->count();
                        
                        $debt = $count * 5000;
                        
                        return $debt > 0 ? "Hutang: Rp " . number_format($debt, 0, ',', '.') : 'Lunas';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'Hutang') ? 'danger' : 'success'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
