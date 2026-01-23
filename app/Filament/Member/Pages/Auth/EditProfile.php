<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('current_password')
                    ->label('Password Lama')
                    ->password()
                    ->revealable()
                    ->required(fn ($get) => filled($get('password')))
                    ->currentPassword()
                    ->dehydrated(false),
                $this->getPasswordFormComponent()
                    ->label('Password Baru'),
                $this->getPasswordConfirmationFormComponent()
                    ->label('Konfirmasi Password Baru'),
            ]);
    }
}

