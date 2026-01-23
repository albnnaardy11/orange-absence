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
use Filament\Forms\Form;

class LiveAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Live Attendance (QR)';
    protected static ?string $title = 'Live Attendance QR';
    protected string $view = 'filament.pages.live-attendance';
    protected static string | \UnitEnum | null $navigationGroup = 'Absence Management';
    protected static ?int $navigationSort = -5;

    public $division_id;
    public $qrCode;
    public $secret;
    public $active_code;

    public function mount()
    {
        // Default to first division if secretary
        if (Auth::user()->hasRole('secretary')) {
            $this->division_id = Auth::user()->divisions()->first()?->id;
        }
        
        $this->secret = bin2hex(random_bytes(16));
        $this->generateQr();
    }

    public function form(Form $form): Form
    {
        return $form
            ->components([
                Select::make('division_id')
                    ->label('Select Division to Project')
                    ->options(function() {
                        if (Auth::user()->hasRole('super_admin')) {
                            return Division::pluck('name', 'id');
                        }
                        return Auth::user()->divisions()->pluck('name', 'id');
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateQr()),
            ]);
    }

    public function generateQr()
    {
        if (!$this->division_id) {
            $this->qrCode = null;
            $this->active_code = null;
            return;
        }

        // Fetch active code for this division today (System A sync)
        $this->active_code = \App\Models\VerificationCode::where('division_id', $this->division_id)
            ->where('date', now()->toDateString())
            ->where('is_active', true)
            ->value('code');

        // Optimized payload to keep QR density low
        $payload = [
            'division_id' => (int) $this->division_id,
            'timestamp' => now()->timestamp,
            'secret' => $this->secret, 
        ];

        // Use standard Crypt which is compatible with decrypt() on the receiver end
        $encrypted = Crypt::encrypt($payload);

        // Generate SVG and store as string to be rendered in Blade
        $this->qrCode = (string) QrCode::size(400)
            ->color(251, 109, 16) // Orange #FB6D10
            ->margin(1)
            ->generate($encrypted);
    }

    public function refreshQr()
    {
        $this->generateQr();
        $this->dispatch('qr-refreshed');
    }
}

