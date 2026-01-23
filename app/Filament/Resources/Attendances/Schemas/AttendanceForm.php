<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alfa' => 'Alfa',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Waktu Absen'),
                Forms\Components\FileUpload::make('proof_image')
                    ->label('Bukti')
                    ->image()
                    ->disk('public')
                    ->directory('attendance-proofs'),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan / Alasan')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Disetujui Admin')
                    ->columnSpanFull(),
            ]);
    }
}
