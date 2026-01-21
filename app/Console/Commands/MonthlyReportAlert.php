<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class MonthlyReportAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monthly-report-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to secretaries to download monthly report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $secretaries = User::role(['secretary', 'super_admin'])->get();

        foreach ($secretaries as $user) {
            Notification::make()
                ->title('Monthly Report Available')
                ->body('The attendance report for this month is ready for download.')
                ->warning() // Use warning or info color
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(url('/admin/attendances')) 
                        ->markAsRead(),
                ])
                ->sendToDatabase($user);
        }

        $this->info('Notifications sent to ' . $secretaries->count() . ' users.');
    }
}
