<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Division;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DivisionDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Kehadiran per Divisi (Bulan Ini)';
    
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Attendance::select('division_id', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'hadir')
            ->groupBy('division_id')
            ->with('division')
            ->get();

        $labels = [];
        $counts = [];

        foreach ($data as $item) {
            $labels[] = $item->division->name ?? 'Unknown';
            $counts[] = $item->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Hadir',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
