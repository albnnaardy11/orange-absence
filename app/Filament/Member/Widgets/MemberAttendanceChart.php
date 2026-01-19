<?php

namespace App\Filament\Member\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MemberAttendanceChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Kehadiran Saya';
    
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $user = Auth::user();
        
        $data = Attendance::where('user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present for the chart
        $statuses = [
            'hadir' => $data['hadir'] ?? 0,
            'izin' => $data['izin'] ?? 0,
            'sakit' => $data['sakit'] ?? 0,
            'alfa' => $data['alfa'] ?? 0,
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Status Kehadiran',
                    'data' => array_values($statuses),
                    'backgroundColor' => [
                        '#22c55e', // hadir - green
                        '#eab308', // izin - yellow
                        '#0ea5e9', // sakit - blue
                        '#ef4444', // alfa - red
                    ],
                ],
            ],
            'labels' => ['Hadir', 'Izin', 'Sakit', 'Alfa'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
