<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationCodeService
{
    public function validate(string $code, int $divisionId, int $userId, $userLat = null, $userLong = null)
    {
        return $this->processValidation($code, $divisionId, $userId, $userLat, $userLong);
    }

    public function validateQR(int $divisionId, int $userId, int $codeId, $userLat = null, $userLong = null)
    {
        $verificationCode = VerificationCode::find($codeId);
        
        if (!$verificationCode || !$verificationCode->is_active || $verificationCode->expires_at < now()) {
             throw new \Exception('Sesi absensi ini sudah berakhir atau tidak aktif.');
        }

        return $this->processValidation(null, $divisionId, $userId, $userLat, $userLong, $verificationCode);
    }

    private function processValidation(?string $code, int $divisionId, int $userId, $userLat, $userLong, $verificationCode = null)
    {
        // Fallback for location (Trim and ensure we handle empty strings from frontend)
        $userLat = trim($userLat ?: request()->cookie('user_lat') ?: ($_COOKIE['user_lat'] ?? ''));
        $userLong = trim($userLong ?: request()->cookie('user_long') ?: ($_COOKIE['user_long'] ?? ''));

        // Convert to null if empty to trigger the detection error
        $userLat = is_numeric($userLat) ? (float) $userLat : null;
        $userLong = is_numeric($userLong) ? (float) $userLong : null;

        $division = \App\Models\Division::find($divisionId);
        
        // 0. Holiday Check
        if (\App\Models\Holiday::where('date', now()->toDateString())->exists()) {
            throw new \Exception('Hari ini adalah hari libur, sistem absensi dinonaktifkan.');
        }

        // 0.1 Division Membership Check (Strict Isolation)
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->divisions->contains($divisionId)) {
            \Illuminate\Support\Facades\Log::warning("DIVISION_MISMATCH_SERVICE: User {$userId} tried to access division {$divisionId}");
            throw new \Exception('Maaf, Anda tidak terdaftar di divisi ini.');
        }

        // 1. Geofencing Check (10 meter strict as per request)
        if ($division && $division->latitude && $division->longitude) {
            if (!$userLat || !$userLong) {
                throw new \Exception('Lokasi GPS tidak terdeteksi. Silakan aktifkan GPS.');
            }

            $distance = $this->calculateDistance($userLat, $userLong, $division->latitude, $division->longitude);
            
            if ($distance > ($division->radius + 5)) { // Beri toleransi minimal 5 meter
                 throw new \Exception("Anda berada di luar area eskul, jangan bolos yaaa");
            }
        }

        // 2. Fetch/Validate Code if not provided via QR
        if (!$verificationCode) {
            $verificationCode = VerificationCode::where('code', $code)
                ->where('division_id', $divisionId)
                ->where('date', now()->toDateString())
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verificationCode) {
                throw new \Exception('Kode verifikasi tidak valid atau kedaluwarsa.');
            }
        }

        // 3. Time Window Check
        $now = now();
        $startTime = $verificationCode->start_at ? $verificationCode->start_at->copy()->subMinutes(30) : now()->subMinutes(60);
        $endTime = $verificationCode->expires_at ? $verificationCode->expires_at->copy()->addMinutes(30) : now()->addMinutes(60);

        if (!$now->between($startTime, $endTime)) {
            throw new \Exception('Sesi absensi belum dimulai atau sudah berakhir.');
        }

        // 4. SMART LOCK (Strict 1x per day total for the user)
        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($todayAttendance) {
            throw new \Exception('Smart Lock: Anda sudah melakukan absensi hari ini. Tidak diijinkan melakukan absensi lebih dari 1x sehari.');
        }

        return [
            'verification_code_id' => $verificationCode->id,
            'schedule_id' => $verificationCode->schedule_id,
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
