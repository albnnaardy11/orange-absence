<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCashLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash:generate-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly unpaid cash logs for all members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::role('member')->get();
        $date = now()->startOfWeek()->toDateString(); // Monday

        $count = 0;
        foreach ($users as $user) {
            // Check if log already exists for this week
            $exists = \App\Models\CashLog::where('user_id', $user->id)
                ->where('date', $date)
                ->exists();

            if (!$exists) {
                \App\Models\CashLog::create([
                    'user_id' => $user->id,
                    'division_id' => $user->divisions()->first()?->id, // Optional linkage
                    'amount' => 5000,
                    'status' => 'unpaid',
                    'date' => $date,
                ]);
                $count++;
            }
        }

        $this->info("Successfully generated {$count} cash logs for date {$date}.");
    }
}
