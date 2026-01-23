<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Division;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';
    protected array | int | null $columns = 3;

    protected function getStats(): array
    {
        $totalMembers = User::role('member')->count();
        $absencesToday = Attendance::whereDate('created_at', Carbon::today())->count();
        $attendanceRate = $totalMembers > 0 ? round(($absencesToday / $totalMembers) * 100, 1) : 0;

        return [
            Stat::make('Total Member', $totalMembers)
                ->description('Jumlah seluruh anggota')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Absen Hari Ini', $absencesToday)
                ->description('Anggota yang sudah absen hari ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Tingkat Kehadiran', $attendanceRate . '%')
                ->description('Persentase kehadiran hari ini')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color($attendanceRate > 70 ? 'success' : 'warning'),
        ];
    }
}

