<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\CashLog;

class AttendanceObserver
{
    /**
     * Handle the Attendance "saved" event.
     * This covers both created and updated events.
     */
    public function saved(Attendance $attendance): void
    {
        // Only proceed if status is TRUE (Verified)
        if ($attendance->status === true) {
            // Check if CashLog already exists to prevent duplicates ("ghost cash logs")
            if ($attendance->cashLog()->exists()) {
                return;
            }

            // Create CashLog
            CashLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'division_id' => $attendance->division_id,
                'amount' => 5000, // Configurable nominal, hardcoded for now or use config()
                'status' => 'unpaid',
            ]);
        }
    }
}
