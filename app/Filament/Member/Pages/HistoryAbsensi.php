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
            ->query(Attendance::query()->where('user_id', Auth::id()))
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

    public function getViewData(): array
    {
        $user = Auth::user();
        
        $allDivisionIds = \Illuminate\Support\Facades\DB::table('division_user')
            ->where('user_id', $user->id)
            ->pluck('division_id')
            ->toArray();
            
        $attendedDivisionIds = \App\Models\Attendance::where('user_id', $user->id)
            ->pluck('division_id')
            ->unique()
            ->toArray();
            
        $allDivisionIds = array_unique(array_merge($allDivisionIds, $attendedDivisionIds));

        $schedules = \App\Models\Schedule::query()
            ->with(['division.users' => function($query) {
                // Try to find users with secretary role in this division
                // Note: role is global in Spatie, so we just get users who 'have' the role
            }])
            ->whereIn('division_id', $allDivisionIds)
            ->where('status', 'active')
            ->get();

        return [
            'schedules' => $schedules,
        ];
    }

    public function getSecretary( \App\Models\Division $division)
    {
        // Find a user in this division who has the 'secretary' role
        return $division->users()
            ->role('secretary')
            ->first()?->name ?? 'Lecturer';
    }
}
