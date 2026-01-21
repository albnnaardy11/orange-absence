<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CashLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CashLogCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CashLog $cashLog
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
            'title' => '💰 Tagihan Kas Baru',
            'body' => sprintf(
                'Kamu memiliki tagihan kas sebesar Rp %s untuk divisi %s. Status: Belum Lunas.',
                number_format($this->cashLog->amount, 0, ',', '.'),
                $this->cashLog->division->name
            ),
            'cash_log_id' => $this->cashLog->id,
            'amount' => $this->cashLog->amount,
            'icon' => 'heroicon-o-banknotes',
            'iconColor' => 'warning',
        ];
    }
}
