<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\User;
use Filament\Notifications\Notification;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        $user = $attendance->user;
        if (!$user) {
            return;
        }

        $points = 0;
        $reason = '';

        if ($attendance->status === 'alfa') {
            $points = 10;
            $reason = 'Absent (Alfa) on ' . $attendance->created_at->format('Y-m-d');
        } elseif (in_array($attendance->status, ['izin', 'sakit'])) {
            $points = 2;
            $reason = ucfirst($attendance->status) . ' on ' . $attendance->created_at->format('Y-m-d');
        }

        if ($points > 0) {
            $this->addPoints($user, $points, $reason);
        }
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        // Optional: Handle status changes if requirements evolve.
        // Currently focused on new data as per instructions.
    }

    protected function addPoints(User $user, int $amount, string $reason): void
    {
        $user->increment('total_points', $amount);
        
        // Log points
        $user->pointLogs()->create([
            'amount' => $amount,
            'reason' => $reason,
        ]);

        $this->checkThresholds($user);
    }

    protected function checkThresholds(User $user): void
    {
        // Reload user to get fresh points
        $user->refresh();

        if ($user->total_points >= 30 && $user->is_active) {
            $user->update(['is_active' => false]);
            
            Notification::make()
                ->title('Account Locked')
                ->body("User {$user->name} has been locked due to excessive points ({$user->total_points}).")
                ->danger()
                ->sendToDatabase(\App\Models\User::role(['super_admin', 'secretary'])->get());

            $this->sendExternalNotification($user, 'Account Locked');
        } elseif ($user->total_points >= 20) {
            Notification::make()
                ->title('Point Warning')
                ->body("User {$user->name} has reached {$user->total_points} points.")
                ->warning()
                ->sendToDatabase(\App\Models\User::role(['super_admin', 'secretary'])->get()); // Also notify user?
                
             // Notify the user themselves too
             Notification::make()
                ->title('Warning: High Points')
                ->body("You have reached {$user->total_points} penalty points. Account will be locked at 30.")
                ->warning()
                ->sendToDatabase($user);

            $this->sendExternalNotification($user, 'Point Warning');
        }
    }

    protected function sendExternalNotification(User $user, string $type): void
    {
        // Placeholder for WhatsApp/Email integration
    }
}
