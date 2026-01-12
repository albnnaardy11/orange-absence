<?php

namespace App\Filament\Widgets;

use App\Models\CashLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArrearsOverview extends BaseWidget
{
    protected static ?int $sort = -1;
    
    protected int | string | array $columnSpan = 'full';
    protected array | int | null $columns = 2;
    protected function getStats(): array
    {
        $totalArrears = CashLog::overdue()->sum('amount');
        $totalIncome = CashLog::where('status', 'paid')->sum('amount');

        return [
            Stat::make('Total Seluruh Tunggakan', 'Rp ' . number_format($totalArrears, 0, ',', '.'))
                ->description('Total akumulasi tunggakan uang kas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
            Stat::make('Total Pendapatan KAS', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description('Total uang kas yang sudah masuk')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
