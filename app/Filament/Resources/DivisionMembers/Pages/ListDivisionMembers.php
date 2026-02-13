<?php

namespace App\Filament\Resources\DivisionMembers\Pages;

use App\Filament\Resources\DivisionMembers\DivisionMemberResource;
use App\Models\Division;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListDivisionMembers extends ListRecords
{
    protected static string $resource = DivisionMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('tambah_member')
                ->label('Tambah Member ke Divisi')
                ->icon('heroicon-m-user-plus')
                ->color('primary')
                ->form([
                    Select::make('user_id')
                        ->label('Pilih User')
                        ->placeholder('Cari nama atau email...')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => User::where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                        ->required(),
                    Select::make('division_ids')
                        ->label('Pilih Divisi')
                        ->multiple()
                        ->options(Division::pluck('name', 'id'))
                        ->required()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $user = User::findOrFail($data['user_id']);
                    
                    // Berikan role member jika belum ada
                    if (!$user->hasRole('member')) {
                        $user->assignRole('member');
                    }
                    
                    // Sync divisi (tanpa menghapus yang lama jika ada, atau gunakan sync jika ingin mengganti)
                    // User request says "memasukan... ke suatu divisi", syncWithoutDetaching seems safer.
                    $user->divisions()->syncWithoutDetaching($data['division_ids']);
                })
                ->successNotificationTitle('Member berhasil ditambahkan ke divisi'),
        ];
    }
}

