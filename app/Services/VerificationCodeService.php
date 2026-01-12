<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationCodeService
{
    public function validate(string $code, int $divisionId, int $userId)
    {
        // 1. Check if code exists and belongs to division
        $verificationCode = VerificationCode::where('code', $code)
            ->where('division_id', $divisionId)
            ->where('date', now()->toDateString())
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            throw new \Exception('Kode verifikasi tidak valid, sudah non-aktif, atau kedaluwarsa.');
        }

        // 2. Determine the current schedule for the division
        $today = now()->format('l'); // Monday, Tuesday...
        
        $schedule = Schedule::where('division_id', $divisionId)
            ->where('day', $today)
            ->first();

        // 3. Time Window Check ( +/- 30 Minutes Tolerance)
        if ($schedule) {
            $now = now();
            $startTime = Carbon::parse($schedule->start_time)->subMinutes(30);
            $endTime = Carbon::parse($schedule->end_time)->addMinutes(30);

            if (!$now->between($startTime, $endTime)) {
                throw new \Exception('Anda hanya bisa absen dalam rentang waktu jadwal (toleransi 30 menit).');
            }
        }

        // 4. Duplicate Check
        $exists = Attendance::where('user_id', $userId)
            ->where('division_id', $divisionId)
            ->where('verification_code_id', $verificationCode->id)
            ->exists();

        if ($exists) {
            throw new \Exception('Anda sudah melakukan absensi untuk sesi ini.');
        }

        return [
            'verification_code_id' => $verificationCode->id,
            'schedule_id' => $schedule?->id,
        ];
    }
}
