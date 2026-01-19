<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\CashLog;
use App\Services\VerificationCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class JadwalKelas extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected string $view = 'filament.member.pages.jadwal-kelas';

    protected static ?string $title = 'Class Schedule';

    public function getSchedules()
    {
        $user = Auth::user();
        if (!$user) return collect();

        $divisionIds = $user->divisions()->pluck('divisions.id');

        return Schedule::whereIn('division_id', $divisionIds)
            ->where('status', 'active')
            ->where('day', now()->format('l'))
            ->where('end_time', '>', now()->format('H:i:s'))
            ->with('division')
            ->get();
    }

    public function absenAction(): Action
    {
        return Action::make('absen')
            ->label('Check-in Now')
            ->color('primary')
            ->button()
            ->extraAttributes([
                'class' => 'w-full justify-center shadow-sm font-bold',
            ])
            ->form([
                TextInput::make('code')
                    ->label('Attendance Code')
                    ->required(),
            ])
            ->action(function (array $data, array $arguments, VerificationCodeService $service) {
                $throttleKey = 'absen-submit:' . Auth::id();
                
                if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                    $seconds = RateLimiter::availableIn($throttleKey);
                    Notification::make()
                        ->title('Too many attempts')
                        ->body("Please wait {$seconds} seconds.")
                        ->danger()
                        ->send();
                    return;
                }

                RateLimiter::hit($throttleKey, 60);

                $scheduleId = $arguments['schedule_id'];
                $schedule = Schedule::find($scheduleId);

                if (!$schedule) {
                    Notification::make()->title('Schedule not found')->danger()->send();
                    return;
                }

                try {
                    $result = $service->validate($data['code'], $schedule->division_id, Auth::id());

                    $attendance = Attendance::create([
                        'user_id' => Auth::id(),
                        'division_id' => $schedule->division_id,
                        'verification_code_id' => $result['verification_code_id'],
                        'schedule_id' => $schedule->id,
                        'status' => 'hadir',
                    ]);

                    // Link to unpaid cash log
                    $cashLog = CashLog::where('user_id', Auth::id())
                        ->where('status', 'unpaid')
                        ->latest()
                        ->first();

                    if ($cashLog) {
                        $cashLog->update(['attendance_id' => $attendance->id]);
                    }

                    Notification::make()->title('Attendance successful!')->success()->send();

                } catch (\Exception $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                }
            });
    }
}
