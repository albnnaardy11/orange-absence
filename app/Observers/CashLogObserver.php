<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CashLog;
use App\Notifications\CashLogCreatedNotification;
use Illuminate\Support\Facades\Notification;

class CashLogObserver
{
    /**
     * Handle the CashLog "created" event.
     */
    public function created(CashLog $cashLog): void
    {
        // Kirim notifikasi ke member bahwa ada tagihan kas baru
        if ($cashLog->user && $cashLog->status === 'unpaid') {
            $cashLog->user->notify(new CashLogCreatedNotification($cashLog));
        }
    }

    /**
     * Handle the CashLog "updated" event.
     */
    public function updated(CashLog $cashLog): void
    {
        // Bisa ditambahkan logika notifikasi saat status berubah jadi paid
        // Tapi untuk sekarang kita skip dulu
    }

    /**
     * Handle the CashLog "deleted" event.
     */
    public function deleted(CashLog $cashLog): void
    {
        //
    }

    /**
     * Handle the CashLog "restored" event.
     */
    public function restored(CashLog $cashLog): void
    {
        //
    }

    /**
     * Handle the CashLog "force deleted" event.
     */
    public function forceDeleted(CashLog $cashLog): void
    {
        //
    }
}
