<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\CashLog;
use Illuminate\Support\Facades\Auth;

class RiwayatKas extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string | \UnitEnum | null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.member.pages.riwayat-kas';

    protected static ?string $title = 'Cash History';

    public function table(Table $table): Table
    {
        return $table
            ->query(CashLog::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('created_at')->dateTime()->label('Date')->sortable(),
                TextColumn::make('division.name')->label('Division'),
                TextColumn::make('amount')->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                    }),
            ]);
    }
}
