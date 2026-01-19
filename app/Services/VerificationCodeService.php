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
        // 0. Holiday Check
        if (\App\Models\Holiday::where('date', now()->toDateString())->exists()) {
            throw new \Exception('Hari ini adalah hari libur, sistem absensi dinonaktifkan.');
        }

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

        // 2. Time Window Check
        // If code works by schedule, check start_at / expires_at
        if ($verificationCode->schedule_id && $verificationCode->start_at && $verificationCode->expires_at) {
            $now = now();
            $startTime = $verificationCode->start_at->copy()->subMinutes(30);
            $endTime = $verificationCode->expires_at->copy()->addMinutes(30);

            if (!$now->between($startTime, $endTime)) {
                throw new \Exception('Anda hanya bisa absen dalam rentang waktu jadwal (toleransi 30 menit).');
            }

            $schedule = $verificationCode->schedule;
        } else {
            // Legacy / Manual code fallback: Find schedule manually
            // Only check "expires_at" for validity (done in query above), but if we want strict window:
            
            $today = now()->format('l'); 
            $schedule = Schedule::where('division_id', $divisionId)
                ->where('day', $today)
                ->first();

            // Perform strict check if schedule exists
            if ($schedule) {
                $now = now();
                $startTime = Carbon::parse($schedule->start_time)->subMinutes(30);
                $endTime = Carbon::parse($schedule->end_time)->addMinutes(30);

                if (!$now->between($startTime, $endTime)) {
                    throw new \Exception('Anda hanya bisa absen dalam rentang waktu jadwal (toleransi 30 menit).');
                }
            }
        }

        // 3. Duplicate Check
        $exists = Attendance::where('user_id', $userId)
            ->where('division_id', $divisionId)
            ->where('verification_code_id', $verificationCode->id)
            ->exists();

        if ($exists) {
            throw new \Exception('Anda sudah melakukan absensi untuk sesi ini.');
        }

        return [
            'verification_code_id' => $verificationCode->id,
            'schedule_id' => $schedule?->id ?? $verificationCode->schedule_id,
        ];
    }
}
