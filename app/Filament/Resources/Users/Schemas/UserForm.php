<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Filament\Models\Contracts\FilamentUser;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->columns(2)
                    ->helperText('Select user roles'),
                Forms\Components\TextInput::make('points')
                    ->numeric()
                    ->default(0)
                    ->label('Penalty Points')
                    ->helperText('Akun otomatis terkunci jika poin >= 30'),
                Forms\Components\Toggle::make('is_suspended')
                    ->label('Suspended')
                    ->onColor('danger')
                    ->offColor('success')
                    ->disabled(fn ($record) => $record && ($record->hasRole('super_admin') || $record->hasRole('secretary')))
                    ->helperText(fn ($record) => $record && ($record->hasRole('super_admin') || $record->hasRole('secretary')) 
                        ? 'Admin/Sekretaris tidak dapat di-suspend.' 
                        : 'Geser untuk menangguhkan akun member secara manual.')
                    ->rules([
                        fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                            // This is a bit tricky in rules during form fill, better to use disabled
                        },
                    ]),
            ]);
    }
}

