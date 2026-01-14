<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Division;
use App\Models\VerificationCode;

class GenerateDivisionCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-division-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random 6-digit codes for all divisions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('l');
        $date = now()->toDateString();
        
        // Find Schedules for today where the Division has auto-generate enabled
        $schedules = \App\Models\Schedule::where('day', $today)
            ->whereHas('division', function ($query) {
                $query->where('is_auto_generate', true);
            })
            ->get();

        foreach ($schedules as $schedule) {
            // Check if code already exists to avoid duplicates
            $exists = VerificationCode::where('schedule_id', $schedule->id)
                ->where('date', $date)
                ->exists();

            if ($exists) {
                continue;
            }

            // Deactivate old/other codes for this division/schedule if any (cleanup)
            VerificationCode::where('division_id', $schedule->division_id)
                ->where('date', $date)
                ->where('schedule_id', $schedule->id)
                ->update(['is_active' => false]);

            // Generate new code
            VerificationCode::create([
                'division_id' => $schedule->division_id,
                'schedule_id' => $schedule->id,
                'code' => sprintf("%06d", mt_rand(1, 999999)),
                'date' => $date,
                'start_at' => now()->setTimeFromTimeString($schedule->start_time),
                'expires_at' => now()->setTimeFromTimeString($schedule->end_time),
                'is_active' => true,
            ]);
        }

        $this->info('Successfully generated division codes for ' . $schedules->count() . ' schedules.');
    }
}
