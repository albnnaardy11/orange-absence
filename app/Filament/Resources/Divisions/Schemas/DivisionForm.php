<?php

namespace App\Filament\Resources\Divisions\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class DivisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
            ]);
    }
}
