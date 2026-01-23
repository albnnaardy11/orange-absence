<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class HolidayForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
            ]);
    }
}

