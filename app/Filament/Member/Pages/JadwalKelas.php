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
use Filament\Forms;
use App\Models\Division;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class JadwalKelas extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | \UnitEnum | null $navigationGroup = 'Kegiatan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.member.pages.jadwal-kelas';

    protected static ?string $title = 'Class Schedule';

    protected $schedules = null;

    public function getSchedules()
    {
        if ($this->schedules !== null) {
            return $this->schedules;
        }

        $user = Auth::user();
        if (!$user) return collect();

        $divisionIds = $user->divisions()->pluck('divisions.id');

        $now = now()->format('H:i:s');
        $today = now()->format('l');
        $yesterday = now()->subDay()->format('l');

        return $this->schedules = Schedule::whereIn('division_id', $divisionIds)
            ->where('status', 'active')
            ->where(function ($query) use ($today, $yesterday, $now) {
                // 1. Scheduled for Today
                $query->where(function ($q) use ($today, $now) {
                    $q->where('day', $today)
                      ->where(function ($timeQ) use ($now) {
                          // Normal schedule: must generally end after now to be relevant
                          $timeQ->where(function ($normal) use ($now) {
                              $normal->whereColumn('start_time', '<=', 'end_time')
                                     ->where('end_time', '>', $now);
                          })
                          // Overnight schedule (starts today, ends tomorrow): always relevant today
                          ->orWhere(function ($overnight) {
                              $overnight->whereColumn('start_time', '>', 'end_time');
                          });
                      });
                })
                // 2. Scheduled for Yesterday (but overnight ending today)
                ->orWhere(function ($q) use ($yesterday, $now) {
                    $q->where('day', $yesterday)
                      ->whereColumn('start_time', '>', 'end_time') // Overnight
                      ->where('end_time', '>', $now); // Still active today
                });
            })
            ->with('division')
            ->orderBy('start_time')
            ->get();
    }

    public function absenAction(): Action
    {
        return Action::make('absen')
            ->label('Masukan Kode')
            ->color('primary')
            ->button()
            ->extraAttributes([
                'class' => 'w-full justify-center shadow-sm font-bold',
            ])
            ->form([
                TextInput::make('code')
                    ->label('Kode Verifikasi')
                    ->placeholder('Contoh: 123456')
                    ->required()
                    ->numeric(),
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
            ->action(function (array $data, array $arguments, VerificationCodeService $service) {
                $throttleKey = 'absen-submit:' . Auth::id();
                
                if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                    $seconds = RateLimiter::availableIn($throttleKey);
                    Notification::make()
                        ->title('Terlalu banyak percobaan')
                        ->body("Tunggu {$seconds} detik lagi.")
                        ->danger()
                        ->send();
                    return;
                }

                RateLimiter::hit($throttleKey, 60);

                $scheduleId = $arguments['schedule_id'];
                $schedule = Schedule::find($scheduleId);

                if (!$schedule) {
                    Notification::make()->title('Jadwal tidak ditemukan')->danger()->send();
                    return;
                }

                try {
                    $result = $service->validate(
                        $data['code'], 
                        $schedule->division_id, 
                        Auth::id(),
                        $data['user_lat'] ?? null,
                        $data['user_long'] ?? null
                    );

                    $attendance = Attendance::create([
                        'user_id' => Auth::id(),
                        'division_id' => $schedule->division_id,
                        'verification_code_id' => $result['verification_code_id'],
                        'schedule_id' => $schedule->id,
                        'status' => 'hadir',
                        'is_approved' => true, // Auto Approve for Verified Code
                        'latitude' => $data['user_lat'] ?? null,
                        'longitude' => $data['user_long'] ?? null,
                    ]);

                    // Link to unpaid cash log
                    $cashLog = CashLog::where('user_id', Auth::id())
                        ->where('status', 'unpaid')
                        ->latest()
                        ->first();

                    if ($cashLog) {
                        $cashLog->update(['attendance_id' => $attendance->id]);
                    }

                    Notification::make()
                        ->title('Absensi Berhasil!')
                        ->body("Absen kode tercatat di {$schedule->division->name}")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                }
            });
    }
}
