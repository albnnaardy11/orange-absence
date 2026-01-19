<?php

namespace App\Filament\Member\Pages;

use App\Models\Attendance;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PengajuanIzin extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-plus';

    protected static string | \UnitEnum | null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.member.pages.pengajuan-izin';

    protected static ?string $navigationLabel = 'Pengajuan Izin/Sakit';

    protected static ?string $title = 'Form Pengajuan Izin / Sakit';

    public ?array $data = [];

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
                Forms\Components\Select::make('status')
                    ->label('Keterangan')
                    ->options([
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Alasan / Pesan')
                    ->required()
                    ->placeholder('Misal: Sakit demam, Ada keperluan keluarga mendadak'),
                Forms\Components\FileUpload::make('proof_image')
                    ->label('Bukti (Foto Surat/Chat)')
                    ->image()
                    ->disk('public')
                    ->directory('attendance-proofs')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        
        // Find current schedule for the division
        $today = now()->format('l');
        $schedule = \App\Models\Schedule::where('division_id', $data['division_id'])
            ->where('day', $today)
            ->first();

        Attendance::create([
            'user_id' => Auth::id(),
            'division_id' => $data['division_id'],
            'schedule_id' => $schedule?->id,
            'status' => $data['status'],
            'description' => $data['description'],
            'proof_image' => $data['proof_image'],
            'is_approved' => false,
        ]);

        Notification::make()
            ->title('Pengajuan Terkirim')
            ->body('Permohonan izin/sakit Anda telah terkirim dan menunggu persetujuan Admin/Sekretaris.')
            ->success()
            ->send();

        $this->form->fill();
    }
}
