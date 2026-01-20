<?php

namespace App\Exports;

use App\Models\Division;
use App\Models\Attendance;
use App\Models\CashLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportExport implements FromCollection, WithHeadings, WithStyles
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $divisions = Division::all();
        $report = collect();

        foreach ($divisions as $division) {
            $totalSchedules = \App\Models\Schedule::where('division_id', $division->id)->count();
            // This is a bit simplified, ideally we count actual sessions in that month
            $totalAttendances = Attendance::where('division_id', $division->id)
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->where('status', 'hadir')
                ->count();
            
            $totalIzinSakit = Attendance::where('division_id', $division->id)
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->whereIn('status', ['izin', 'sakit'])
                ->count();

            $totalUnpaidCash = CashLog::whereHas('user.divisions', function($q) use ($division) {
                    $q->where('divisions.id', $division->id);
                })
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->where('status', 'unpaid')
                ->sum('amount');

            $totalPaidCash = CashLog::whereHas('user.divisions', function($q) use ($division) {
                    $q->where('divisions.id', $division->id);
                })
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->where('status', 'paid')
                ->sum('amount');

            $report->push([
                'Division' => $division->name,
                'Total Hadir' => $totalAttendances,
                'Total Izin/Sakit' => $totalIzinSakit,
                'Total Kas Lunas (Rp)' => number_format($totalPaidCash, 0, ',', '.'),
                'Total Tunggakan (Rp)' => number_format($totalUnpaidCash, 0, ',', '.'),
            ]);
        }

        return $report;
    }

    public function headings(): array
    {
        return [
            'Nama Divisi',
            'Kehadiran (Hadir)',
            'Izin/Sakit',
            'Pemasukan Kas (Lunas)',
            'Total Tunggakan Kas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
