<?php

namespace App\Filament\Resources\DivisionMembers;

use App\Filament\Resources\DivisionMembers\Pages;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionMemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = 'Division Member';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Division Members';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['divisions'])
            ->whereHas('roles', fn ($query) => $query->where('name', 'member'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->readOnly(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->readOnly(),

                Forms\Components\Select::make('divisions')
                    ->label('Divisions')
                    ->relationship('divisions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('divisions.name')
                    ->label('Divisions')
                    ->badge()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisions')
                    ->relationship('divisions', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Filter per Divisi'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDivisionMembers::route('/'),
            'create' => Pages\CreateDivisionMember::route('/create'),
            'edit' => Pages\EditDivisionMember::route('/{record}/edit'),
        ];
    }
}
