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

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('division_id')
                    ->label('Pilih Divisi')
                    ->options(fn () => Auth::check() ? Auth::user()->divisions()->pluck('name', 'id') : [])
                    ->required(),
                Forms\Components\Hidden::make('qr_payload'),
            ])
            ->statePath('data');
    }

    public ?string $qr_payload = null;

    public function saveAttendance(?string $payloadStr = null, $lat = null, $long = null): void
    {
        $userId = Auth::id();
        
        // 1. Data Gathering
        $qrPayload = $payloadStr ?? $this->qr_payload;
        $userLat = $lat ?? $this->user_lat;
        $userLong = $long ?? $this->user_long;

        // Logging
        \Illuminate\Support\Facades\Log::info("QR_SCAN_ATTEMPT: User: {$userId}", [
            'payload_present' => !empty($qrPayload),
            'lat' => $userLat,
            'long' => $userLong
        ]);

        try {
            // 2. Initial Validation
            if (empty($qrPayload)) {
                throw new \Exception("Tidak ada data QR yang terbaca.");
            }

            // 3. Decryption
            try {
                $payload = \Illuminate\Support\Facades\Crypt::decrypt($qrPayload);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("QR_DECRYPT_FAIL: User {$userId} - " . $e->getMessage());
                throw new \Exception("QR Code tidak valid! Pastikan Anda scan QR resmi dari portal.");
            }

            if (!is_array($payload) || !isset($payload['division_id']) || !isset($payload['timestamp'])) {
                throw new \Exception("Format QR rusak atau tidak dikenali.");
            }

            // 4. Timestamp Check (Relaxed to 5 minutes to account for slow connections/devices)
            $secondsDiff = abs(now()->timestamp - $payload['timestamp']);
            if ($secondsDiff > 300) { 
                 throw new \Exception("QR Code kadaluwarsa. Silakan refresh layar Admin.");
            }

            // 5. Division Validation
            $division = Division::find($payload['division_id']);
            if (!$division) {
                 throw new \Exception("Divisi dalam QR tidak ditemukan di sistem.");
            }

            // 6. Geofencing (If Enforced)
            if ($division->latitude && $division->longitude) {
                if (!$userLat || !$userLong) {
                     // If device didn't send GPS, we suspect browser permission issue
                     throw new \Exception("Lokasi tidak terdeteksi. Izinkan akses GPS di browser Anda.");
                }

                $distance = $this->calculateDistance($userLat, $userLong, $division->latitude, $division->longitude);
                
                // Allow up to 300m for "GPS drift" or improved UX
                if ($distance > 300) { 
                    \Illuminate\Support\Facades\Log::warning("GEOFENCE_FAIL: User {$userId} is {$distance}m away.");
                    throw new \Exception("Anda terlalu jauh ({$distance} meter) dari lokasi absen.");
                }
            }

            // 7. Duplicate Check
            $exists = Attendance::where('user_id', $userId)
                ->where('division_id', $division->id)
                ->whereDate('created_at', now()->today())
                ->exists();

            if ($exists) {
                 // Send success event anyway so UI stops loading, but show warning
                 $this->dispatch('attendance-success'); 
                 Notification::make()->title('Sudah Absen')->body('Anda sudah absen hari ini.')->warning()->send();
                 return;
            }

            // 8. Find Schedule
            $activeSchedule = Schedule::where('division_id', $division->id)
                ->where('day', now()->format('l'))
                ->whereTime('start_time', '<=', now())
                ->whereTime('end_time', '>=', now())
                ->first();

            // 9. Create Record
            $attendance = Attendance::create([
                'user_id' => $userId,
                'division_id' => $division->id,
                'schedule_id' => $activeSchedule?->id,
                'status' => 'hadir',
                'is_approved' => true,
                'latitude' => $userLat,
                'longitude' => $userLong,
                'is_qr_verified' => true,
                'verified_at' => now(),
            ]);

            \Illuminate\Support\Facades\Log::info("QR_SUCCESS: User {$userId} Attendance ID: {$attendance->id}");

            // 10. Success UI
            $classroom = $activeSchedule ? $activeSchedule->classroom : $division->name;
            Notification::make()
                ->title('Berhasil!')
                ->body("Absen tercatat di {$classroom}")
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

    public function submit(?string $manualPayload = null): void
    {
        $userId = Auth::id();
        $throttleKey = 'absen-manual:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            Notification::make()->title('Terlalu banyak mencoba')->danger()->send();
            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $data = $this->form->getState();
        $qrPayload = $manualPayload ?? $data['qr_payload'];

        try {
            if (empty($qrPayload)) {
                throw new \Exception("Silakan scan QR Code terlebih dahulu.");
            }

            // Decrypt QR
            try {
                $payload = \Illuminate\Support\Facades\Crypt::decrypt($qrPayload);
            } catch (\Exception $e) {
                throw new \Exception("QR Code tidak valid.");
            }

            if (!isset($payload['division_id']) || !isset($payload['timestamp'])) {
                throw new \Exception("Payload QR tidak lengkap.");
            }

            $divisionId = $data['division_id'] ?? $payload['division_id'];

            if (abs(now()->timestamp - $payload['timestamp']) > 180) {
                throw new \Exception("QR Code sudah kadaluwarsa.");
            }

            // Geofencing
            $division = Division::find($divisionId);
            if ($division && $division->latitude && $division->longitude && $this->user_lat && $this->user_long) {
                if ($this->calculateDistance($this->user_lat, $this->user_long, $division->latitude, $division->longitude) > 200) {
                    throw new \Exception("Anda di luar jangkauan radius.");
                }
            }

            $alreadyAttended = Attendance::where('user_id', $userId)
                ->where('division_id', $divisionId)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyAttended) {
                throw new \Exception("Anda sudah melakukan absensi untuk divisi ini hari ini.");
            }

            $nowTime = now()->format('H:i:s');
            $currentDay = now()->format('l');
            $activeSchedule = Schedule::where('division_id', $divisionId)
                ->where('day', $currentDay)
                ->where('start_time', '<=', $nowTime)
                ->where('end_time', '>=', $nowTime)
                ->first();

            Attendance::create([
                'user_id' => $userId,
                'division_id' => $divisionId,
                'schedule_id' => $activeSchedule?->id,
                'status' => 'hadir',
                'is_approved' => true,
                'latitude' => $this->user_lat,
                'longitude' => $this->user_long,
                'is_qr_verified' => true,
                'verified_at' => now(),
            ]);

            Notification::make()->title('Absensi Berhasil!')->success()->send();
            $this->form->fill();

        } catch (\Exception $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return round($dist * 60 * 1.1515 * 1.609344 * 1000);
    }
}
