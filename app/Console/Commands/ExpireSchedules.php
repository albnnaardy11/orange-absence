<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire active schedules and delete verification codes after end_time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $today = $now->format('l');
        $currentTime = $now->format('H:i:s');

        $expiredSchedules = \App\Models\Schedule::where('day', $today)
            ->where('status', 'active')
            ->where('end_time', '<', $currentTime)
            ->get();

        if ($expiredSchedules->isEmpty()) {
            $this->info('No expired schedules found.');
            return;
        }

        foreach ($expiredSchedules as $schedule) {
            $schedule->update(['status' => 'finished']);

            // Delete associated verification codes
            \App\Models\VerificationCode::where('schedule_id', $schedule->id)
                ->where('date', $now->toDateString())
                ->delete();

            $this->info("Schedule [{$schedule->id}] for [{$schedule->division->name}] has been marked as finished and codes deleted.");
        }

        $this->info('Expiration process completed.');
    }
}
