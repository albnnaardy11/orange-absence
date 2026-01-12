<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
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
            ])
            ->filters([
                SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->visible(fn () => Auth::check() && Auth::user()->hasRole('super_admin')),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        return response()->streamDownload(function () use ($livewire) {
                            $records = $livewire->getFilteredTableQuery()->get();
                            $handle = fopen('php://output', 'w');
                            
                            // Add UTF-8 BOM for Excel compatibility
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
                        }, 'attendance_' . now()->format('Y-m-d') . '.csv');
                    }),
            ])
            ->recordActions([
                Action::make('mark_hadir')
                    ->label('Mark Hadir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Attendance $record) => $record->update(['status' => 'hadir']))
                    ->visible(fn (Attendance $record) => $record->status !== 'hadir'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
