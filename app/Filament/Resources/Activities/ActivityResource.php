<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    protected static string|\UnitEnum|null $navigationGroup = 'System Audit';

    protected static ?string $slug = 'activities';

    protected static ?string $label = 'Activity Log';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->searchable()
                    ->placeholder('System'),
                TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(fn ($state) => str_replace('App\\Models\\', '', $state ?? '')),
                TextColumn::make('description')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('properties.ip')
                    ->label('IP Address')
                    ->searchable(),
                TextColumn::make('properties.user_agent')
                    ->label('Perangkat')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('description')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
