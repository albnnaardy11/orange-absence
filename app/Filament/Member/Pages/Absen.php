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

    public function handleQrScan($qrPayload, $lat, $long): void
    {
        $userId = Auth::id();
        \Illuminate\Support\Facades\Log::info("User {$userId} attempting QR Scan.");

        $throttleKey = 'absen-qr:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            \Illuminate\Support\Facades\Log::warning("User {$userId} QR Scan rate limited.");
            Notification::make()->title('Terlalu banyak mencoba')->danger()->send();
            return;
        }
        RateLimiter::hit($throttleKey, 60);

        try {
            if (empty($qrPayload)) {
                throw new \Exception("Payload QR kosong.");
            }

            // Sync User Location from JS
            $this->user_lat = $lat;
            $this->user_long = $long;

            // Decrypt QR
            try {
                $payload = \Illuminate\Support\Facades\Crypt::decrypt($qrPayload);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("User {$userId} Invalid QR: " . $e->getMessage());
                throw new \Exception("QR Code tidak valid atau rusak.");
            }

            // Validation basics
            if (!isset($payload['division_id']) || !isset($payload['timestamp'])) {
                throw new \Exception("Format QR tidak dikenali.");
            }

            $divisionId = $payload['division_id'];

            // Expiry check (60 seconds)
            if (abs(now()->timestamp - $payload['timestamp']) > 60) {
                 throw new \Exception("QR Code sudah kadaluwarsa. Minta Admin refresh QR.");
            }

            // Check distance
            $division = Division::find($divisionId);
            if (!$division) {
                 throw new \Exception("Divisi tidak ditemukan.");
            }

            // Geofencing Check
            if ($division->latitude && $division->longitude) {
                if (!$this->user_lat || !$this->user_long) {
                     throw new \Exception("Lokasi GPS Anda belum terdeteksi. Pastikan GPS aktif.");
                }

                $distance = $this->calculateDistance($this->user_lat, $this->user_long, $division->latitude, $division->longitude);
                if ($distance > 100) {
                    \Illuminate\Support\Facades\Log::warning("User {$userId} Geofence fail. Dist: {$distance}m");
                    throw new \Exception("Anda berada di luar jangkauan radius absen ($distance meter). Maksimal 100m.");
                }
            }

            // Check duplicate
            $alreadyAttended = Attendance::where('user_id', Auth::id())
                ->where('division_id', $divisionId)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyAttended) {
                Notification::make()->title('Info')->body('Anda sudah absen di divisi ini hari ini.')->warning()->send();
                return;
            }

            // Create Attendance
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'division_id' => $divisionId,
                'status' => 'hadir',
                'is_approved' => true,
                'latitude' => $this->user_lat,
                'longitude' => $this->user_long,
                'is_qr_verified' => true,
            ]);

            \Illuminate\Support\Facades\Log::info("User {$userId} success QR attendance ID: " . $attendance->id);

            Notification::make()
                ->title('Berhasil Absen!')
                ->body('Absensi Anda telah tercatat.')
                ->success()
                ->send();
            
            $this->dispatch('attendance-success'); 
            $this->form->fill(); 

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("User {$userId} QR Error: " . $e->getMessage());
            Notification::make()->title('Gagal Absen')->body($e->getMessage())->danger()->send();
            // Re-throw so frontend knows it failed? No, Notification is better info, but we need frontend to stop spinner
            throw $e; 
        }
    }

    public function submit(?string $manualPayload = null): void
    {
        $throttleKey = 'absen-qr:' . Auth::id();
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

            // Validation basics
            if (!isset($payload['division_id']) || !isset($payload['timestamp'])) {
                throw new \Exception("Payload QR tidak lengkap.");
            }

            // Auto-detect division if not selected
            $divisionId = $data['division_id'] ?? $payload['division_id'];

            if ($payload['division_id'] != $divisionId) {
                throw new \Exception("QR Code ini untuk divisi lain.");
            }

            // Expiry check (60 seconds)
            if (abs(now()->timestamp - $payload['timestamp']) > 60) {
                throw new \Exception("QR Code sudah kadaluwarsa, silakan minta Admin refresh QR.");
            }

            // Geofencing
            $division = Division::find($divisionId);
            if ($division && $division->latitude && $division->longitude && $this->user_lat && $this->user_long) {
                if ($this->calculateDistance($this->user_lat, $this->user_long, $division->latitude, $division->longitude) > 100) {
                    throw new \Exception("Anda di luar jangkauan (>100m).");
                }
            }

            // Check if already attended today for this division/session
            $alreadyAttended = Attendance::where('user_id', Auth::id())
                ->where('division_id', $divisionId)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyAttended) {
                throw new \Exception("Anda sudah melakukan absensi untuk divisi ini hari ini.");
            }

            // Create Attendance
            Attendance::create([
                'user_id' => Auth::id(),
                'division_id' => $divisionId,
                'status' => 'hadir',
                'is_approved' => true,
                'latitude' => $this->user_lat,
                'longitude' => $this->user_long,
                'is_qr_verified' => true,
            ]);

            Notification::make()->title('Absensi QR Berhasil!')->body('Anda sudah absen!')->success()->send();
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
