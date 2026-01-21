<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class HistoryAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.member.pages.history-absensi';

    protected static ?string $title = 'Attendance History';

    public function table(Table $table): Table
    {
        return $table
            ->query(Attendance::query()->where('user_id', Auth::id())->with(['division', 'schedule']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Division'),
                TextColumn::make('schedule.classroom')
                    ->label('Classroom')
                    ->default('-'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alfa' => 'Alfa',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'izin' => 'warning',
                        'sakit' => 'info',
                        'alfa' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Simplified class: View data for schedules is no longer needed since we show personal info.
}
