<?php

namespace App\Observers;

use App\Models\Schedule;
use App\Models\VerificationCode;
use Carbon\Carbon;

class ScheduleObserver
{
    /**
     * Handle the Schedule "created" event.
     */
    public function created(Schedule $schedule): void
    {
        $this->generateCodeIfToday($schedule);
    }

    /**
     * Handle the Schedule "updated" event.
     */
    public function updated(Schedule $schedule): void
    {
        $this->generateCodeIfToday($schedule);
    }

    private function generateCodeIfToday(Schedule $schedule): void
    {
        // If the schedule is active and it's for today
        if ($schedule->status === 'active' && strtolower($schedule->day) === strtolower(now()->format('l'))) {
            
            // Check if code already exists for today
            $exists = VerificationCode::where('schedule_id', $schedule->id)
                ->where('date', now()->toDateString())
                ->exists();

            if (!$exists) {
                VerificationCode::create([
                    'division_id' => $schedule->division_id,
                    'schedule_id' => $schedule->id,
                    'code' => sprintf("%06d", mt_rand(1, 999999)),
                    'date' => now()->toDateString(),
                    'start_at' => Carbon::parse(now()->toDateString() . ' ' . $schedule->start_time),
                    'expires_at' => Carbon::parse(now()->toDateString() . ' ' . $schedule->end_time),
                    'is_active' => true,
                ]);
            }
        }
    }
}
