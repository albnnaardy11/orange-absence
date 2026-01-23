<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\CashLog;

class AttendanceObserver
{
    public function creating(Attendance $attendance): void
    {
        $attendance->ip_address = request()->ip();
        $attendance->user_agent = request()->userAgent();
    }

    /**
     * Handle the Attendance "saved" event.
     * This covers both created and updated events.
     */
    public function saved(Attendance $attendance): void
    {
        // Only proceed if status is 'hadir' (Present) or true (Verified)
        if ($attendance->status === true || $attendance->status === 'hadir') {
            // Check if this attendance is already linked
            if ($attendance->cashLog()->exists()) {
                return;
            }

            $today = now()->toDateString();
            $startOfWeek = now()->startOfWeek()->toDateString();
            $endOfWeek = now()->endOfWeek()->toDateString();

            // 1. Check if ANY Cash Log exists for this user in this week (linked or not)
            $existingLogThisWeek = CashLog::where('user_id', $attendance->user_id)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->first();

            if ($existingLogThisWeek) {
                // If it exists but isn't linked to an attendance yet, link it
                if (!$existingLogThisWeek->attendance_id) {
                    $existingLogThisWeek->update([
                        'attendance_id' => $attendance->id,
                        'division_id' => $attendance->division_id,
                    ]);
                }
                // If it's already linked to another attendance, we do nothing (Member only pays once per week)
            } else {
                // 2. Create new dated CashLog if absolutely no bill exists for this week yet
                CashLog::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'division_id' => $attendance->division_id,
                    'amount' => 5000,
                    'status' => 'unpaid',
                    'date' => $today,
                ]);
            }
        }

        // Point System & Auto-Lock Logic
        // Status: 'alfa' (+10), 'izin' (+2), 'val_sakit' (+2)
        // Adjust points based on status if it's a new record or status changed
        if ($attendance->wasRecentlyCreated || $attendance->isDirty('status')) {
            $pointsToAdd = 0;
            
            // Normalize status to lowercase string
            $status = is_string($attendance->status) ? strtolower($attendance->status) : '';

            if ($status === 'alfa') {
                $pointsToAdd = 10;
            } elseif (in_array($status, ['izin', 'sakit', 'val_izin', 'val_sakit'])) {
                $pointsToAdd = 2;
            }

            if ($pointsToAdd > 0) {
                 /** @var \App\Models\User $user */
                $user = $attendance->user;
                $user->increment('points', $pointsToAdd);

                // Point notification
                \Filament\Notifications\Notification::make()
                    ->title('Poin Pelanggaran Bertambah')
                    ->body("Poin Anda bertambah +{$pointsToAdd} (Status: " . strtoupper($status) . "). Total poin Anda sekarang: {$user->points}. Akun akan dikunci otomatis jika mencapai 30 poin!")
                    ->warning()
                    ->sendToDatabase($user);

                // Auto-Suspension Check (Only for Members)
                if ($user->hasRole('member') && $user->refresh()->points >= 30 && !$user->is_suspended) {
                    $user->update(['is_suspended' => true]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Akun Ditangguhkan!')
                        ->body('Akun Anda telah dikunci otomatis karena poin mencapai 30.')
                        ->danger()
                        ->persistent()
                        ->sendToDatabase($user);
                }
            }
        }
    }
}
