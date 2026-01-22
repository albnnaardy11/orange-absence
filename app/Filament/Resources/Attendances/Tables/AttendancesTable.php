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
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Approved')
                    ->formatStateUsing(function ($state, Attendance $record) {
                        // Hanya tampilkan status approval untuk izin/sakit
                        if (in_array($record->status, ['izin', 'sakit'])) {
                            return $state ? '✓ Disetujui' : 'Menunggu';
                        }
                        // Untuk hadir dan alfa, tidak perlu approval
                        return '-';
                    })
                    ->badge()
                    ->color(function ($state, Attendance $record) {
                        if (in_array($record->status, ['izin', 'sakit'])) {
                            return $state ? 'success' : 'warning';
                        }
                        return 'gray';
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
                SelectFilter::make('week')
                    ->label('Minggu Ke')
                    ->options(function () {
                        $options = [];
                        $startOfMonth = now()->startOfMonth();
                        $endOfMonth = now()->endOfMonth();
                        $currentDate = $startOfMonth->copy();
                        
                        $weekNumber = 1;
                        while ($currentDate->lte($endOfMonth)) {
                            $startOfWeek = $currentDate->copy()->startOfWeek();
                            $endOfWeek = $currentDate->copy()->endOfWeek();
                            
                            $value = $startOfWeek->format('Y-m-d') . ',' . $endOfWeek->format('Y-m-d');
                            $label = "Minggu ke-{$weekNumber} ({$startOfWeek->format('d M')} - {$endOfWeek->format('d M')})";
                            
                            $options[$value] = $label;
                            
                            $currentDate->addWeek();
                            $weekNumber++;
                        }
                        
                        return $options;
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            [$start, $end] = explode(',', $data['value']);
                            $query->whereBetween('created_at', [
                                \Carbon\Carbon::parse($start)->startOfDay(),
                                \Carbon\Carbon::parse($end)->endOfDay()
                            ]);
                        }
                    })
                    ->default(function () {
                        $now = now();
                        return $now->startOfWeek()->format('Y-m-d') . ',' . $now->endOfWeek()->format('Y-m-d');
                    }),
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
