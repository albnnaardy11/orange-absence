<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $divisions = \App\Models\Division::where('is_auto_generate', true)->get();
        $date = now()->toDateString();
        $expiresAt = now()->setTime(17, 0, 0);

        foreach ($divisions as $division) {
            // Deactivate old codes for today
            \App\Models\VerificationCode::where('division_id', $division->id)
                ->where('date', $date)
                ->update(['is_active' => false]);

            // Generate new code
            \App\Models\VerificationCode::create([
                'division_id' => $division->id,
                'code' => sprintf("%06d", mt_rand(1, 999999)),
                'date' => $date,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
        }

        $this->info('Successfully generated vision codes for ' . $divisions->count() . ' divisions.');
    }
}
