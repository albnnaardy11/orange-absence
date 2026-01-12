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

class Absen extends Page implements HasForms
{
    use InteractsWithForms;

    // protected static $navigationIcon = 'heroicon-o-document-text';

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
            ])
            ->statePath('data');
    }

    public function submit(VerificationCodeService $service): void
    {
        $data = $this->form->getState();

        try {
            $result = $service->validate($data['code'], $data['division_id'], Auth::id());

            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'division_id' => $data['division_id'],
                'verification_code_id' => $result['verification_code_id'],
                'schedule_id' => $result['schedule_id'] ?? null,
                'status' => 'hadir',
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
