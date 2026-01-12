<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWeeklyCash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-weekly-cash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly cash bill for members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::role('member')->get();
        $date = now()->toDateString();
        
        $count = 0;
        foreach ($users as $user) {
            // Avoid duplicate billing for the same day
            $exists = \App\Models\CashLog::where('user_id', $user->id)
                ->where('date', $date)
                ->exists();

            if (!$exists) {
                \App\Models\CashLog::create([
                    'user_id' => $user->id,
                    'amount' => 5000,
                    'status' => 'unpaid',
                    'date' => $date,
                ]);
                $count++;
            }
        }

        $this->info("Successfully generated weekly cash for {$count} members.");
    }
}
