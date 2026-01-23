<?php

namespace App\Filament\Resources\CashLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CashLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'division']))
            ->columns([
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('division.name')
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('overdue_status')
                    ->label('Status Tagihan')
                    ->options([
                        'overdue' => 'Tunggakan (Lewat Deadline)',
                        'pending' => 'Baru Ditagih (Belum Deadline)',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'overdue') {
                            return $query->where('status', 'unpaid')
                                ->where(function ($q) {
                                    $q->whereNotNull('date')
                                        ->whereRaw("DATE_ADD(date, INTERVAL (3 - WEEKDAY(date)) DAY) < CURDATE()")
                                        ->orWhere(function ($sq) {
                                            $sq->whereRaw("DATE_ADD(date, INTERVAL (3 - WEEKDAY(date)) DAY) = CURDATE()")
                                                ->whereRaw("CURTIME() > '17:00:00'");
                                        });
                                });
                        }

                        if ($data['value'] === 'pending') {
                            return $query->where('status', 'unpaid')
                                ->where(function ($q) {
                                    $q->whereNotNull('date')
                                        ->whereRaw("DATE_ADD(date, INTERVAL (3 - WEEKDAY(date)) DAY) > CURDATE()")
                                        ->orWhere(function ($sq) {
                                            $sq->whereRaw("DATE_ADD(date, INTERVAL (3 - WEEKDAY(date)) DAY) = CURDATE()")
                                                ->whereRaw("CURTIME() <= '17:00:00'");
                                        });
                                });
                        }
                    }),
            ])
            ->recordActions([
                Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(fn ($record) => $record->update(['status' => 'paid'])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_paid_bulk')
                        ->label('Tandai Lunas')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'paid'])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function ($livewire) {
                        return response()->streamDownload(function () use ($livewire) {
                            $records = $livewire->getFilteredTableQuery()->get();
                            $handle = fopen('php://output', 'w');
                            
                            // Add UTF-8 BOM for Excel compatibility
                            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($handle, ['Member', 'Division', 'Amount', 'Status', 'Date', 'Created At']);
                            
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->user->name,
                                    $record->division?->name ?? 'Naturale/Weekly',
                                    $record->amount,
                                    strtoupper($record->status),
                                    $record->date?->format('Y-m-d') ?? 'N/A',
                                    $record->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }
                            fclose($handle);
                        }, 'cash_logs_' . now()->format('Y-m-d') . '.csv');
                    }),
            ]);
    }
}

