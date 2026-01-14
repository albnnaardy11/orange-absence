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
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['roles'])->withCount(['cashLogs as overdue_count' => fn ($q) => $q->overdue()]))
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
                        $count = $record->overdue_count;
                        $debt = $count * 5000;
                        
                        return $debt > 0 
                            ? "Nunggak {$count}x (Rp " . number_format($debt, 0, ',', '.') . ")" 
                            : 'Lunas';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'Nunggak') ? 'danger' : 'success'),
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
