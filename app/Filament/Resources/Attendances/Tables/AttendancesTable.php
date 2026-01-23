<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['user', 'division', 'schedule']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'izin' => 'warning',
                        'sakit' => 'info',
                        'alfa' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu Absen')
                    ->dateTime()
                    ->sortable(),
                ImageColumn::make('proof_image')
                    ->label('Bukti')
                    ->disk('public')
                    ->size(40)
                    ->circular()
                    ->openUrlInNewTab(),
                TextColumn::make('is_approved')
                    ->label('Verification')
                    ->badge()
                    ->getStateUsing(fn (Attendance $record): string => match (true) {
                        $record->status === 'hadir' => 'Hadir',
                        $record->is_approved => 'Approved',
                        default => 'Waiting',
                    })
                    ->color(fn (Attendance $record): string => match (true) {
                        $record->status === 'hadir' => 'success',
                        $record->is_approved => 'success',
                        default => 'danger',
                    })
                    ->icon(fn (Attendance $record): string => match (true) {
                        $record->status === 'hadir' => 'heroicon-m-minus-small',
                        $record->is_approved => 'heroicon-m-check-badge',
                        default => 'heroicon-m-clock',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->visible(fn () => Auth::check() && Auth::user()->hasRole('super_admin')),
                SelectFilter::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alfa' => 'Alfa',
                    ]),
                \Filament\Tables\Filters\Filter::make('attendance_period')
                    ->form([
                        \Filament\Forms\Components\Select::make('period_type')
                            ->label('Periode')
                            ->options([
                                'this_week' => 'Minggu Ini',
                                'last_week' => 'Minggu Lalu',
                                'this_month' => 'Bulan Ini',
                                'last_month' => 'Bulan Lalu',
                                'custom_range' => 'Rentang Tanggal Custom',
                                'all' => 'Semua Waktu',
                            ])
                            ->default('this_week'),
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\DatePicker::make('from')
                                    ->label('Dari')
                                    ->visible(fn ($get) => $get('period_type') === 'custom_range'),
                                \Filament\Forms\Components\DatePicker::make('until')
                                    ->label('Sampai')
                                    ->visible(fn ($get) => $get('period_type') === 'custom_range'),
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['period_type'] === 'this_week',
                                fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                            )
                            ->when(
                                $data['period_type'] === 'last_week',
                                fn ($q) => $q->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                            )
                            ->when(
                                $data['period_type'] === 'this_month',
                                fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)
                            )
                            ->when(
                                $data['period_type'] === 'last_month',
                                fn ($q) => $q->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)
                            )
                            ->when(
                                $data['period_type'] === 'custom_range',
                                fn ($q) => $q
                                    ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                                    ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['period_type'] || $data['period_type'] === 'all') return null;
                        
                        $labels = [
                            'this_week' => 'Minggu Ini',
                            'last_week' => 'Minggu Lalu',
                            'this_month' => 'Bulan Ini',
                            'last_month' => 'Bulan Lalu',
                            'custom_range' => 'Rentang Custom',
                        ];
                        
                        return 'Periode: ' . ($labels[$data['period_type']] ?? $data['period_type']);
                    })
            ])
            ->headerActions([
                Action::make('monthly_report')
                    ->label('Laporan Bulanan')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            $records = Attendance::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->with(['user', 'division'])
                                ->get();
                                
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($handle, ['LAPORAN ABSENSI BULAN ' . strtoupper(now()->format('F Y'))]);
                            fputcsv($handle, []);
                            fputcsv($handle, ['Member', 'Division', 'Status', 'Tanggal', 'Waktu']);
                            
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->user->name,
                                    $record->division->name,
                                    ucfirst($record->status),
                                    $record->created_at->format('Y-m-d'),
                                    $record->created_at->format('H:i:s'),
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_bulanan_' . now()->format('Y_m') . '.csv');
                    }),
                Action::make('export_filtered')
                    ->label('Export Filtered')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        return response()->streamDownload(function () use ($livewire) {
                            $records = $livewire->getFilteredTableQuery()->get();
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($handle, ['Member', 'Division', 'Status', 'Schedule', 'Waktu Absen']);
                            
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->user->name,
                                    $record->division->name,
                                    ucfirst($record->status),
                                    $record->schedule?->day . ' ' . $record->schedule?->start_time ?? 'N/A',
                                    $record->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }
                            fclose($handle);
                        }, 'attendance_filtered_' . now()->format('Y-m-d') . '.csv');
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Attendance $record) => $record->update(['is_approved' => true]))
                    ->visible(fn (Attendance $record) => in_array($record->status, ['izin', 'sakit']) && !$record->is_approved),
                Action::make('mark_hadir')
                    ->label('Mark Hadir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Attendance $record) => $record->update(['status' => 'hadir', 'is_approved' => true]))
                    ->visible(fn (Attendance $record) => $record->status === 'alfa'),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

