<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\VerificationCode;
use Carbon\Carbon;

class GenerateDailyCodes extends Command
{
    protected $signature = 'app:generate-daily-codes';
    protected $description = 'Generate verification codes for all active schedules for today';

    public function handle()
    {
        $today = now()->format('l');
        $dateStr = now()->toDateString();
        
        $schedules = Schedule::where('day', $today)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($schedules as $schedule) {
            $exists = VerificationCode::where('schedule_id', $schedule->id)
                ->where('date', $dateStr)
                ->exists();

            if (!$exists) {
                VerificationCode::create([
                    'division_id' => $schedule->division_id,
                    'schedule_id' => $schedule->id,
                    'code' => sprintf("%06d", mt_rand(1, 999999)),
                    'date' => $dateStr,
                    'start_at' => Carbon::parse($dateStr . ' ' . $schedule->start_time),
                    'expires_at' => Carbon::parse($dateStr . ' ' . $schedule->end_time),
                    'is_active' => true,
                ]);
                $count++;
            }
        }

        $this->info("Successfully generated {$count} verification codes for today.");
    }
}
