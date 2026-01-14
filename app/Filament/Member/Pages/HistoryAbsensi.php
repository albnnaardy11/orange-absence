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

    protected string $view = 'filament.member.pages.history-absensi';

    protected static ?string $title = 'Riwayat Kehadiran';

    public function table(Table $table): Table
    {
        return $table
            ->query(Attendance::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Divisi'),
                TextColumn::make('schedule.classroom')
                    ->label('Ruang Kelas')
                    ->default('-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'ijin' => 'warning',
                        'alpha' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
