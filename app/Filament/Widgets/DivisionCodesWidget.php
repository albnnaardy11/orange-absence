<?php

namespace App\Filament\Widgets;

use App\Models\VerificationCode;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;

class DivisionCodesWidget extends BaseWidget
{
    protected static ?string $heading = 'Division Code Management';
    
    protected static ?int $sort = -2;
    
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VerificationCode::query()
                    ->whereDate('date', now()->toDateString())
                    ->whereNotNull('schedule_id')
                    ->when(
                        Auth::check() && !Auth::user()->hasRole('super_admin'),
                        fn ($query) => $query->whereIn('division_id', Auth::user()->divisions->pluck('id'))
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division'),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('info')
                    ->copyMessage('Code copied to clipboard')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Start')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('End')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->headerActions([
                // Manual creation removed as per request (now automated via Schedule)
                Action::make('automation')
                    ->label('Manage Automation')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('gray')
                    ->modalHeading('Automated Generation Settings')
                    ->form(function () {
                        $divisions = \App\Models\Division::all();
                        if (!Auth::user()->hasRole('super_admin')) {
                            $divisions = Auth::user()->divisions;
                        }
                        
                        return $divisions->map(function ($division) {
                            return Toggle::make('auto_' . $division->id)
                                ->label('Auto Generate: ' . $division->name)
                                ->default($division->is_auto_generate)
                                ->live()
                                ->afterStateUpdated(fn ($state) => $division->update(['is_auto_generate' => $state]));
                        })->toArray();
                    })
                    ->modalSubmitAction(false),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('code')->required(),
                        DateTimePicker::make('expires_at')->required(),
                        Toggle::make('is_active'),
                    ])
                    ->after(fn () => $this->dispatch('refresh-active-codes')),
                DeleteAction::make()
                    ->after(fn () => $this->dispatch('refresh-active-codes')),
                Action::make('regenerate')
                    ->label('Regen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (VerificationCode $record) {
                        $record->update(['is_active' => false]);
                        
                        VerificationCode::create([
                            'division_id' => $record->division_id,
                            'schedule_id' => $record->schedule_id,
                            'code' => sprintf("%06d", mt_rand(1, 999999)),
                            'date' => $record->date,
                            'expires_at' => $record->expires_at,
                            'is_active' => true,
                        ]);
                        
                        $this->dispatch('refresh-active-codes');
                    }),
            ])
            ->paginated(false);
    }
}
