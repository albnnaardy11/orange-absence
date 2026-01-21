<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use App\Models\Division;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class LiveAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Live Attendance (QR)';
    protected static ?string $title = 'Live Attendance QR';
    protected string $view = 'filament.pages.live-attendance';
    protected static string | \UnitEnum | null $navigationGroup = 'Absence Management';

    public $division_id;
    public $qrCode;
    public $secret;

    public function mount()
    {
        // Default to first division if admin has one, usually for secretary
        if (Auth::user()->hasRole('secretary')) {
            $this->division_id = Auth::user()->divisions()->first()?->id;
        }
        
        // Generate initial secret
        $this->secret = bin2hex(random_bytes(16));
        $this->generateQr();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('division_id')
                ->label('Select Division to Project')
                ->options(Division::pluck('name', 'id'))
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->generateQr()),
        ];
    }

    public function generateQr()
    {
        if (!$this->division_id) {
            $this->qrCode = null;
            return;
        }

        // Payload: Division ID + Timestamp + Secret (Consistency check)
        // Timestamp is key for Anti-Cheat (Validity window)
        $payload = [
            'division_id' => $this->division_id,
            'timestamp' => now()->timestamp,
            'secret' => $this->secret, 
            // Secret ensures checking consistency, can be rotated per session
        ];

        $encrypted = Crypt::encrypt($payload);

        // Generate SVG
        $this->qrCode = (string) QrCode::size(400)
            ->color(255, 107, 0) // Orange color
            ->generate($encrypted);
    }

    public function refreshQr()
    {
        $this->generateQr();
    }
}
