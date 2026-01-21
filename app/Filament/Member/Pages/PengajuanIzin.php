<?php

namespace App\Filament\Member\Pages;

use App\Models\Attendance;
use App\Notifications\LeaveRequestNotification;
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
                    ->maxSize(512) // Limit to 512KB
                    ->validationMessages([
                        'max' => 'Ukuran foto terlalu besar! Maksimal 500kb agar database tidak bengkak.',
                    ])
                    ->disk('public')
                    ->directory('attendance-proofs')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Check if user has already made any attendance record today for this division
        $alreadyAttended = Attendance::where('user_id', Auth::id())
            ->where('division_id', $data['division_id'])
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyAttended) {
            Notification::make()
                ->title('Gagal Mengajukan')
                ->body('Anda tidak bisa mengajukan izin/sakit karena Anda sudah memiliki catatan absensi (Hadir/Izin/Sakit) hari ini.')
                ->danger()
                ->send();
            return;
        }
        
        // Find current schedule for the division
        $today = now()->format('l');
        $schedule = \App\Models\Schedule::where('division_id', $data['division_id'])
            ->where('day', $today)
            ->first();

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'division_id' => $data['division_id'],
            'schedule_id' => $schedule?->id,
            'status' => $data['status'],
            'description' => $data['description'],
            'proof_image' => $data['proof_image'],
            'is_approved' => false,
        ]);

        // Kirim notifikasi ke member (toast)
        Notification::make()
            ->title('Pengajuan Terkirim')
            ->body('Permohonan izin/sakit Anda telah terkirim dan menunggu persetujuan Admin/Sekretaris.')
            ->success()
            ->send();

        // Kirim notifikasi database ke admin dan sekretaris di divisi yang sama
        $recipients = \App\Models\User::query()
            ->where(function ($query) use ($data) {
                $query->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
                    ->orWhere(function ($q) use ($data) {
                        $q->whereHas('roles', fn ($r) => $r->where('name', 'panel_user'))
                          ->whereHas('divisions', fn ($d) => $d->where('divisions.id', $data['division_id']));
                    });
            })
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new LeaveRequestNotification($attendance));
        }

        $this->form->fill();
    }
}
