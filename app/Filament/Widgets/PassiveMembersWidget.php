<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PassiveMembersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Peringatan: Member Pasif';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::role('member')->whereHas('attendances', function ($query) {
                    $query->where('status', 'alfa');
                }, '>=', 3)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Member'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('divisions.name')->label('Divisi')->badge(),
                Tables\Columns\TextColumn::make('alfa_count')
                    ->label('Total Alfa')
                    ->getStateUsing(fn (User $record) => $record->attendances()->where('status', 'alfa')->count())
                    ->color('danger'),
            ]);
    }
}
