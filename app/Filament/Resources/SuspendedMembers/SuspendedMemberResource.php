<?php

namespace App\Filament\Resources\SuspendedMembers;

use App\Filament\Resources\SuspendedMembers\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;

class SuspendedMemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?string $slug = 'suspended-members';

    protected static ?string $label = 'Suspended Member';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_suspended', true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->disabled()
                    ->label('Name'),
                TextInput::make('email')
                    ->disabled()
                    ->label('Email'),
                TextInput::make('points')
                    ->numeric()
                    ->label('Penalty Points'),
                Toggle::make('is_suspended')
                    ->label('Suspended Status')
                    ->onColor('danger')
                    ->offColor('success')
                    ->disabled(fn ($record) => $record && ($record->hasRole('super_admin') || $record->hasRole('secretary')))
                    ->helperText(fn ($record) => $record && ($record->hasRole('super_admin') || $record->hasRole('secretary')) 
                        ? 'Admin/Sekretaris tidak dapat di-suspend.' 
                        : 'Atur status penangguhan akun.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('points')
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->label('Penalty Points'),
                IconColumn::make('is_suspended')
                    ->boolean()
                    ->label('Suspended')
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->color(fn (string $state): string => $state ? 'danger' : 'success'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('reset_points')
                    ->label('Reset & Unsuspend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update([
                            'points' => 0,
                            'is_suspended' => false,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Akun Telah Diaktifkan')
                            ->body('Poin pelanggaran Anda telah direset dan akun Anda kini dapat digunakan kembali.')
                            ->success()
                            ->sendToDatabase($record);

                        \Filament\Notifications\Notification::make()
                            ->title('Member unsuspended and points reset.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuspendedMembers::route('/'),
            'edit' => Pages\EditSuspendedMember::route('/{record}/edit'),
        ];
    }
}
