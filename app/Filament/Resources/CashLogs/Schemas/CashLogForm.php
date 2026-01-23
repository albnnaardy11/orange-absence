<?php

namespace App\Filament\Resources\CashLogs\Schemas;

use Filament\Forms\Form;
use Filament\Forms;

class CashLogForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->components([
                Forms\Components\Select::make('user_id')
                    ->relationship(
                        'user', 
                        'name', 
                        fn (\Illuminate\Database\Eloquent\Builder $query) => $query->role('member')
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('IDR'),
                Forms\Components\Select::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('date'),
            ]);
    }
}

