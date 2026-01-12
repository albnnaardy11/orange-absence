<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Services\VerificationCodeService;
use Illuminate\Console\Command;

class GenerateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new verification codes for all divisions';

    /**
     * Execute the console command.
     */
    public function handle(VerificationCodeService $service)
    {
        $divisions = Division::all();

        foreach ($divisions as $division) {
            $code = $service->generate($division->id);
            $this->info("Generated code for {$division->name}: {$code->code}");
        }

        $this->info('All codes generated successfully.');
    }
}
