<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Services\VerificationCodeService;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\RateLimiter;

class Absen extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.member.pages.absen';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('division_id')
                    ->label('Division')
                    ->options(fn () => Auth::check() ? Auth::user()->divisions()->pluck('name', 'id') : [])
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('Verification Code')
                    ->required(),
                Forms\Components\Hidden::make('user_lat')
                    ->extraAttributes([
                        'id' => 'user_lat',
                        'x-init' => '$el.value = window.userLat; $el.dispatchEvent(new Event("input"))',
                        'x-on:gps-updated.window' => '$el.value = $event.detail.lat; $el.dispatchEvent(new Event("input"))'
                    ]),
                Forms\Components\Hidden::make('user_long')
                    ->extraAttributes([
                        'id' => 'user_long',
                        'x-init' => '$el.value = window.userLong; $el.dispatchEvent(new Event("input"))',
                        'x-on:gps-updated.window' => '$el.value = $event.detail.long; $el.dispatchEvent(new Event("input"))'
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(VerificationCodeService $service): void
    {
        $throttleKey = 'absen-submit:' . Auth::id();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Notification::make()
                ->title('Too many attempts')
                ->body("Please wait {$seconds} seconds before trying again.")
                ->danger()
                ->send();
            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $data = $this->form->getState();

        try {
            $result = $service->validate(
                $data['code'], 
                $data['division_id'], 
                Auth::id(),
                $data['user_lat'] ?? null,
                $data['user_long'] ?? null
            );

            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'division_id' => $data['division_id'],
                'verification_code_id' => $result['verification_code_id'],
                'schedule_id' => $result['schedule_id'] ?? null,
                'status' => 'hadir',
                'latitude' => $data['user_lat'] ?? null,
                'longitude' => $data['user_long'] ?? null,
            ]);

            // Automatically link to the latest unpaid cash log if exists
            $cashLog = \App\Models\CashLog::where('user_id', Auth::id())
                ->where('status', 'unpaid')
                ->latest()
                ->first();

            if ($cashLog) {
                $cashLog->update(['attendance_id' => $attendance->id]);
            }

            Notification::make()->title('Attendance verified!')->success()->send();
            $this->form->fill(); 

        } catch (\Exception $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
