<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Filament\Notifications\Notification;

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
                TextColumn::make('points')
                    ->label('Pts')
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->visible(fn ($record) => $record && $record->points > 0),
                \Filament\Tables\Columns\IconColumn::make('is_suspended')
                    ->label('Suspended')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->color(fn (string $state): string => $state ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('suspended')
                    ->label('Suspended Members')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->where('is_suspended', true)),
                //
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('reset_points')
                    ->label('Reset')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->points > 0 || $record->is_suspended)
                    ->action(function ($record) {
                        $record->update([
                            'points' => 0,
                            'is_suspended' => false,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Akun Telah Diaktifkan')
                            ->body('Poin pelanggaran Anda telah direset dan akun Anda kini dapat digunakan kembali.')
                            ->success()
                            ->sendToDatabase($record);

                        \Filament\Notifications\Notification::make()
                            ->title('Status Reset Successfully')
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('reset_password')
                    ->label('Reset Pwd')
                    ->icon('heroicon-o-key')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password')
                    ->modalDescription('Reset password user ini menjadi ORENSSOLUTION2026?')
                    ->action(function ($record) {
                        $record->update([
                            'password' => Hash::make('ORENSSOLUTION2026'),
                        ]);
                        
                        Notification::make()
                            ->title('Password Berhasil Direset')
                            ->body("Password untuk {$record->name} telah direset ke default.")
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('reset_passwords_bulk')
                        ->label('Reset Password (Terpilih)')
                        ->icon('heroicon-o-key')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password Massal')
                        ->modalDescription('Reset password semua user yang dipilih menjadi ORENSSOLUTION2026?')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (User $user) {
                                $user->update([
                                    'password' => Hash::make('ORENSSOLUTION2026'),
                                ]);
                            });

                            Notification::make()
                                ->title($records->count() . ' Password Berhasil Direset')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

