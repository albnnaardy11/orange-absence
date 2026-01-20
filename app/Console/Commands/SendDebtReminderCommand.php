<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class SendDebtReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-debt-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic debt reminders to members with 3+ overdue cash logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::role('member')
            ->whereHas('cashLogs', fn ($q) => $q->overdue(), '>=', 3)
            ->with(['cashLogs' => fn ($q) => $q->overdue()])
            ->get();

        $this->info("Checking " . $users->count() . " members for debt reminders...");

        foreach ($users as $user) {
            $overdueCount = $user->cashLogs->count();
            $totalDebt = $user->cashLogs->sum('amount');
            
            Notification::make()
                ->title('⚠️ Peringatan Tunggakan Kas')
                ->body("Halo {$user->name}, kamu memiliki {$overdueCount} tunggakan kas senilai Rp " . number_format($totalDebt, 0, ',', '.') . ". Harap segera melakukan pembayaran ke bendahara/sekretaris.")
                ->warning()
                ->persistent()
                ->actions([
                    Action::make('view_history')
                        ->label('Lihat Riwayat')
                        ->url(url('/member/riwayat-kas'))
                        ->button(),
                ])
                ->sendToDatabase($user);
            
            $this->line("Sent reminder to {$user->name} ({$overdueCount} overdue)");
        }

        $this->info('Debt reminders sent successfully!');
    }
}
