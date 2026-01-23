<?php

namespace App\Filament\Resources\Divisions\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class DivisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(65535),
                Section::make('Geofencing (Validasi Lokasi)')
                    ->description('Setel lokasi pusat kegiatan agar member hanya bisa absen di radius tertentu.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->default(-6.333093)
                                    ->placeholder('-6.xxxxxx')
                                    ->helperText('Contoh: -6.200000'),
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->default(106.897862)
                                    ->placeholder('106.xxxxxx')
                                    ->helperText('Contoh: 106.816666'),
                                TextInput::make('radius')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('Meter')
                                    ->helperText('Jarak maksimal member dari titik pusat.'),

                            ]),
                    ]),
            ]);
    }
}
