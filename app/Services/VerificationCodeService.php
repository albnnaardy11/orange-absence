<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationCodeService
{
    public function generate(int $divisionId)
    {
        // 1. Check for today's schedule
        $day = now()->format('l');
        $schedule = Schedule::where('division_id', $divisionId)
            ->where('day', $day)
            ->first();

        // 2. Determine time range
        $start = now();
        $end = now()->addHours(2); // Default 2 hours if no schedule

        if ($schedule) {
            $start = now()->setTimeFromTimeString($schedule->start_time);
            $end = now()->setTimeFromTimeString($schedule->end_time);
            
            if ($end->lessThan($start)) {
                $end->addDay();
            }
        }

        // 3. Create or Update code
        return VerificationCode::updateOrCreate(
            [
                'division_id' => $divisionId,
                'date' => now()->toDateString(),
            ],
            [
                'schedule_id' => $schedule?->id,
                'code' => sprintf("%06d", mt_rand(1, 999999)),
                'start_at' => $start,
                'expires_at' => $end,
                'is_active' => true,
            ]
        );
    }

    public function validate(string $code, int $divisionId, int $userId, $userLat = null, $userLong = null)
    {
        // Fallback multi-level: Form Arguments -> Laravel Cookie -> Raw PHP Cookie
        $userLat = $userLat ?: request()->cookie('user_lat') ?: ($_COOKIE['user_lat'] ?? null);
        $userLong = $userLong ?: request()->cookie('user_long') ?: ($_COOKIE['user_long'] ?? null);

        $division = \App\Models\Division::find($divisionId);
        
        // 0a. Geofencing Check
        if ($division && $division->latitude && $division->longitude) {
            if (!$userLat || !$userLong) {
                throw new \Exception('Lokasi GPS tidak terdeteksi. Silakan aktifkan GPS dan izinkan akses lokasi.');
            }

            $distance = $this->calculateDistance($userLat, $userLong, $division->latitude, $division->longitude);
            
            if ($distance > ($division->radius + 10)) { // Beri toleransi extra 10 meter
                throw new \Exception("Anda terlalu jauh dari lokasi eskul. jangan bolos yaaaa!");
            }


        }
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

        // 3. Duplicate Check: Check if any attendance exists for this user/division today
        $exists = Attendance::where('user_id', $userId)
            ->where('division_id', $divisionId)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($exists) {
            throw new \Exception('Oops! Anda sudah absen hari ini (via QR/Kode). Tidak perlu absen double ya! ');
        }

        return [
            'verification_code_id' => $verificationCode->id,
            'schedule_id' => $schedule?->id ?? $verificationCode->schedule_id,
        ];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
