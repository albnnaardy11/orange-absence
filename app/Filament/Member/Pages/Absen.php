<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Services\VerificationCodeService;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Division;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Crypt;

class Absen extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = true;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scan QR Absen';
    protected static ?string $title = 'Scan QR Absen';
    protected static string | \UnitEnum | null $navigationGroup = 'Absensi';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.member.pages.absen-qr';

    public ?array $data = [];
    public $user_lat;
    public $user_long;
    public ?string $qr_payload = null;

    public function mount(): void
    {
        $this->form->fill();

        // Interactive Greeting
        if (Auth::check()) {
            $user = Auth::user();
            $attendedToday = Attendance::where('user_id', $user->id)
                ->whereDate('created_at', now()->today())
                ->exists();

            if ($attendedToday) {
                Notification::make()
                    ->title("Hi, {$user->name}!")
                    ->body("Kamu sudah melakukan absensi hari ini. Terima kasih!")
                    ->color('info')
                    ->icon('heroicon-o-check-badge')
                    ->send();
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('division_id')
                    ->label('Pilih Divisi')
                    ->options(fn () => Auth::check() ? Auth::user()->divisions()->pluck('name', 'id') : [])
                    ->required()
                    ->native(false),
                Forms\Components\Hidden::make('qr_payload'),
            ])
            ->statePath('data');
    }

    public function saveAttendance(?string $payloadStr = null, $lat = null, $long = null): void
    {
        $userId = Auth::id();
        $qrPayload = $payloadStr ?? $this->qr_payload;
        $userLat = $lat ?? $this->user_lat;
        $userLong = $long ?? $this->user_long;

        \Illuminate\Support\Facades\Log::info("QR_SCAN_ATTEMPT: User: {$userId}", [
            'payload_present' => !empty($qrPayload),
            'lat' => $userLat,
            'long' => $userLong
        ]);

        try {
            if (empty($qrPayload)) {
                throw new \Exception("Tidak ada data QR yang terbaca.");
            }

            try {
                $payload = Crypt::decrypt($qrPayload);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("QR_DECRYPT_FAIL: User {$userId} - " . $e->getMessage());
                throw new \Exception("QR Code tidak valid! Pastikan Anda scan QR resmi dari portal.");
            }

            if (!is_array($payload) || !isset($payload['division_id']) || !isset($payload['timestamp'])) {
                throw new \Exception("Format QR rusak atau tidak dikenali.");
            }

            // Timestamp Check (300s / 5m tolerance)
            $secondsDiff = abs(now()->timestamp - $payload['timestamp']);
            if ($secondsDiff > 300) { 
                 throw new \Exception("QR Code kadaluwarsa. Silakan refresh layar Admin.");
            }

            $division = Division::find($payload['division_id']);
            if (!$division) {
                 throw new \Exception("Divisi dalam QR tidak ditemukan di sistem.");
            }

            // Division Check (Strict Isolation)
            if (!Auth::user()->divisions->contains($division->id)) {
                 \Illuminate\Support\Facades\Log::warning("DIVISION_MISMATCH: User {$userId} tried to scan for {$division->name}");
                 throw new \Exception("Ups! Kode QR ini untuk Divisi {$division->name}. Anda tidak terdaftar di divisi tersebut.");
            }

            // Geofencing (Strictly using division settings)
            if ($division->latitude && $division->longitude) {
                if (!$userLat || !$userLong) {
                     throw new \Exception("Lokasi tidak terdeteksi. Silakan aktifkan GPS dan refresh halaman.");
                }

                $distance = $this->calculateDistance($userLat, $userLong, $division->latitude, $division->longitude);
                $radius = $division->radius ?? 100; // Default to 100 if not set
                $tolerance = 10; // 10 meter tolerance for GPS drift
                
                if ($distance > ($radius + $tolerance)) { 
                    \Illuminate\Support\Facades\Log::warning("GEOFENCE_FAIL: User {$userId} is {$distance}m away from {$division->name} (Limit: {$radius}m)");
                    throw new \Exception("Anda berada di luar area eskul, jangan bolos yaaa");
                }
            }

            // Duplicate Check (Strict 1x per day total for the user)
            $alreadyAttended = Attendance::where('user_id', $userId)
                ->whereDate('created_at', now()->today())
                ->exists();

            if ($alreadyAttended) {
                 throw new \Exception("Oops! Anda sudah melakukan absensi hari ini. Sistem membatasi hanya 1x absen per hari.");
            }

            // Find Schedule
            $activeSchedule = Schedule::where('division_id', $division->id)
                ->where('day', now()->format('l'))
                ->whereTime('start_time', '<=', now())
                ->whereTime('end_time', '>=', now())
                ->first();

            // Create Record
            $attendance = Attendance::create([
                'user_id' => $userId,
                'division_id' => $division->id,
                'schedule_id' => $activeSchedule?->id,
                'status' => 'hadir',
                'is_approved' => true,
                'latitude' => $userLat,
                'longitude' => $userLong,
            ]);

            \Illuminate\Support\Facades\Log::info("QR_SUCCESS: User {$userId} Attendance ID: {$attendance->id}");

            // Greeting & Quote
            $classroom = $activeSchedule ? $activeSchedule->classroom : $division->name;
            $hour = now()->hour;
            $timeGreeting = match(true) {
                $hour < 11 => "Selamat Pagi!",
                $hour < 15 => "Selamat Siang!",
                $hour < 18 => "Selamat Sore!",
                default => "Selamat Malam!",
            };

            $quotes = [
                "Semangat belajarnya hari ini!",
                "Jangan lupa berdoa sebelum mulai ya!",
                "Keep up the good work!",
                "Masa depan cerah dimulai hari ini!",
                "Selalu jaga kesehatan ya!"
            ];
            $randomQuote = $quotes[array_rand($quotes)];

            Notification::make()
                ->title("{$timeGreeting} Absen Berhasil!")
                ->body("{$randomQuote}\n Tercatat di: {$classroom}")
                ->success()
                ->persistent()
                ->send();
            
            $this->dispatch('attendance-success'); 
            $this->form->fill(); 

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("QR_PROCESS_ERROR: User {$userId}: " . $e->getMessage());
            $this->dispatch('attendance-failure', error: $e->getMessage());
            Notification::make()->title('Gagal')->body($e->getMessage())->danger()->send();
        }
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

