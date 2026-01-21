<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Attendance $attendance
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '📋 Pengajuan ' . ucfirst($this->attendance->status) . ' Baru',
            'body' => sprintf(
                '%s mengajukan %s untuk divisi %s. Alasan: %s',
                $this->attendance->user->name,
                $this->attendance->status,
                $this->attendance->division->name,
                $this->attendance->description ?? '-'
            ),
            'attendance_id' => $this->attendance->id,
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Lihat & Approve',
                    'url' => route('filament.admin.resources.attendances.attendances.edit', ['record' => $this->attendance->id]),
                ],
            ],
        ];
    }

    /**
     * Convert notification to database format for Filament
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
