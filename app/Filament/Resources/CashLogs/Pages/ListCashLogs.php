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
            \Filament\Actions\Action::make('sendReminders')
                ->label('Kirim Pengingat Tunggakan')
                ->icon('heroicon-o-bell-alert')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kirim Notifikasi Tunggakan')
                ->modalDescription('Sistem akan mengirimkan notifikasi ke semua member yang menunggak kas >= 3 kali.')
                ->action(fn () => \Illuminate\Support\Facades\Artisan::call('app:send-debt-reminder'))
                ->visible(fn () => auth()->user()->hasRole('super_admin')),
            CreateAction::make(),
        ];
    }
}
