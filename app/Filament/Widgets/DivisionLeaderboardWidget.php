<?php

namespace App\Filament\Widgets;

use App\Models\Division;
use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DivisionLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Leaderboard Divisi Teraktif';

    public function table(Table $table): Table
    {
        return $table
            ->query(Division::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Divisi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Total Member')
                    ->getStateUsing(fn (Division $record) => $record->users()->count())
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('attendance_count')
                    ->label('Total Kehadiran')
                    ->getStateUsing(function (Division $record) {
                        return Attendance::where('division_id', $record->id)
                            ->where('status', 'hadir')
                            ->count();
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_rate')
                    ->label('Tingkat Keaktifan')
                    ->getStateUsing(function (Division $record) {
                        $membersCount = $record->users()->count();
                        if ($membersCount === 0) return '0%';
                        $attendanceCount = Attendance::where('division_id', $record->id)
                            ->where('status', 'hadir')
                            ->count();
                        // This is a simplified rate, maybe over total possible attendances? 
                        // For now let's just show it as a percentage relative to members.
                        return round(($attendanceCount / max(1, $membersCount)) * 100, 1) . '%';
                    })
                    ->badge()
                    ->color('warning'),
            ])
            ->defaultSort('name');
    }
}
