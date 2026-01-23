<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadReport')
                ->label('Download Monthly Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('month')
                        ->label('Pilih Bulan')
                        ->options([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ])
                        ->default(now()->format('m'))
                        ->required(),
                    \Filament\Forms\Components\Select::make('year')
                        ->label('Pilih Tahun')
                        ->options(array_combine(range(date('Y') - 2, date('Y') + 1), range(date('Y') - 2, date('Y') + 1)))
                        ->default(now()->format('Y'))
                        ->required(),
                    \Filament\Forms\Components\Select::make('type')
                        ->label('Format File')
                        ->options([
                            'excel' => 'Excel (.xlsx)',
                            'pdf' => 'PDF (.pdf)',
                        ])
                        ->default('excel')
                        ->required(),
                ])
                ->action(function (array $data) {
                    if ($data['type'] === 'excel') {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\MonthlyReportExport($data['month'], $data['year']),
                            "rekap-bulanan-{$data['month']}-{$data['year']}.xlsx"
                        );
                    } else {
                        // PDF Logic
                        $divisions = \App\Models\Division::all();
                        $reportData = [];
                        
                        $monthNames = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ];

                        foreach ($divisions as $division) {
                            $reportData[] = [
                                'name' => $division->name,
                                'hadir' => \App\Models\Attendance::where('division_id', $division->id)
                                    ->whereMonth('created_at', $data['month'])
                                    ->whereYear('created_at', $data['year'])
                                    ->where('status', 'hadir')
                                    ->count(),
                                'izin_sakit' => \App\Models\Attendance::where('division_id', $division->id)
                                    ->whereMonth('created_at', $data['month'])
                                    ->whereYear('created_at', $data['year'])
                                    ->whereIn('status', ['izin', 'sakit'])
                                    ->count(),
                                'paid' => \App\Models\CashLog::whereHas('user.divisions', fn($q) => $q->where('divisions.id', $division->id))
                                    ->whereMonth('created_at', $data['month'])
                                    ->whereYear('created_at', $data['year'])
                                    ->where('status', 'paid')
                                    ->sum('amount'),
                                'unpaid' => \App\Models\CashLog::whereHas('user.divisions', fn($q) => $q->where('divisions.id', $division->id))
                                    ->whereMonth('created_at', $data['month'])
                                    ->whereYear('created_at', $data['year'])
                                    ->where('status', 'unpaid')
                                    ->sum('amount'),
                            ];
                        }

                        $pdfData = [
                            'data' => $reportData,
                            'monthName' => $monthNames[$data['month']],
                            'year' => $data['year'],
                        ];

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.monthly_report_pdf', $pdfData);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "rekap-bulanan-{$data['month']}-{$data['year']}.pdf");
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}

