<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuspendedMemberResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use App\Models\PointLog;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

use Filament\Schemas\Schema;

class SuspendedMemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Suspended Members';
    protected static ?string $modelLabel = 'Suspended Member';
    protected static ?string $navigationGroup = 'User Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_active', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_points')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Suspended At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('activate')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Pemulihan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update([
                            'is_active' => true,
                            'total_points' => 0,
                        ]);

                        // Log the restoration
                        PointLog::create([
                            'user_id' => $record->id,
                            'amount' => 0, // Reset
                            'reason' => 'Account activated by Admin. Reason: ' . $data['reason'],
                        ]);

                        Notification::make()
                            ->title('Account Activated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuspendedMembers::route('/'),
        ];
    }
}
