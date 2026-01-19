<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CashLog extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'unpaid' || !$this->date) {
            return false;
        }

        // Deadline is Friday 17:00 of the same week (Monday + 4 days)
        $date = Carbon::parse($this->date);
        $deadline = $date->startOfWeek()->addDays(4)->setTime(17, 0, 0);
        
        return now()->greaterThan($deadline);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
            ->whereNotNull('date')
            ->where(function ($q) {
                // Check if current date is past the Friday of the week of 'date'
                // Weekday index: Mon=0, Tue=1, ... Fri=4
                // We want: DATE_ADD(date, INTERVAL (4 - WEEKDAY(date)) DAY) < CURDATE()
                $q->whereRaw("DATE_ADD(date, INTERVAL (4 - WEEKDAY(date)) DAY) < CURDATE()")
                    ->orWhere(function ($sq) {
                        // Or if it IS Friday (deadline day) and time is past 17:00
                        $sq->whereRaw("DATE_ADD(date, INTERVAL (4 - WEEKDAY(date)) DAY) = CURDATE()")
                            ->whereRaw("CURTIME() > '17:00:00'");
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
