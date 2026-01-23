<?php

namespace App\Observers;

use App\Models\User;
use Filament\Notifications\Notification;

class UserObserver
{
    /**
     * Handle the User "saving" event.
     */
    public function saving(User $user): void
    {
        // Auto-Suspension Logic
        // If points reach 30 or more, automatically suspend the user (Only for Members)
        if ($user->hasRole('member') && $user->points >= 30 && !$user->is_suspended) {
            $user->is_suspended = true;

            // Optional: Notify the user (they will see it when they next log in if database notifications are enabled)
            Notification::make()
                ->title('Akun Ditangguhkan Otomatis')
                ->body('Akun Anda telah ditangguhkan karena poin pelanggaran mencapai 30 atau lebih.')
                ->danger()
                ->persistent()
                ->sendToDatabase($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If points were reduced below 30, we could theoretically unsuspend, 
        // but usually un-suspending is a manual administrative action.
        // So we leave it as is for now.
    }
}
