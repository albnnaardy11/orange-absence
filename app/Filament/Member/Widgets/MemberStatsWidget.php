<?php

namespace App\Filament\Member\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Attendance;
use App\Models\CashLog;
use Illuminate\Support\Facades\Auth;

class MemberStatsWidget extends BaseWidget
{
    protected static ?int $sort = -5;

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) return [];

        // Total Attendance Count
        $attendanceCount = Attendance::where('user_id', $user->id)
            ->where('status', 'hadir')
            ->count();

        // Outstanding Cash (Unpaid)
        $unpaidCash = CashLog::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->sum('amount');

        // Active Divisions Count
        $divisionsCount = $user->divisions()->count();

        return [
            Stat::make('Total Attendance', $attendanceCount . ' Sessions')
                ->description('Total times you logged in')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Outstanding Cash', 'Rp ' . number_format($unpaidCash, 0, ',', '.'))
                ->description('Total unpaid cash logs')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($unpaidCash > 0 ? 'danger' : 'gray'),
            Stat::make('Active Divisions', $divisionsCount)
                ->description('Sections you belong to')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}

