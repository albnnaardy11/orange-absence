<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CashLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'unpaid' || !$this->date) {
            return false;
        }

        // Deadline is Thursday 17:00 of the same week
        $date = Carbon::parse($this->date);
        $deadline = $date->startOfWeek()->addDays(3)->setTime(17, 0, 0);
        
        return now()->greaterThan($deadline);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
            ->whereNotNull('date')
            ->where(function ($q) {
                $q->whereRaw("date(date, 'weekday 4') < date('now')")
                    ->orWhere(function ($sq) {
                        $sq->whereRaw("date(date, 'weekday 4') = date('now')")
                            ->whereRaw("time('now', 'localtime') > '17:00:00'");
                    });
            });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function attendance(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
