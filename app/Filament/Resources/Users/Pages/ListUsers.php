<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_all_members')
                ->label('Reset Semua Pass Member')
                ->icon('heroicon-o-key')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Semua Password Member')
                ->modalDescription('Apakah Anda yakin ingin mereset password SEMUA user dengan role member menjadi ORENSSOLUTION2026?')
                ->action(function () {
                    $count = User::role('member')->count();
                    
                    User::role('member')->get()->each(function (User $user) {
                        $user->update([
                            'password' => Hash::make('ORENSSOLUTION2026'),
                        ]);
                    });

                    Notification::make()
                        ->title($count . ' Password Member Berhasil Direset')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}

