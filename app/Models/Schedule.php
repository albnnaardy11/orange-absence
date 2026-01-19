<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Schedule extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['day', 'start_time', 'end_time', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $guarded = [];

    public function division(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    protected static function booted(): void
    {
        static::created(function (Schedule $schedule) {
            \Illuminate\Support\Facades\Log::info("Schedule created event: " . $schedule->id . " Day: " . $schedule->day . " Today: " . now()->format('l'));
            if ($schedule->day === now()->format('l')) {
                static::createVerificationCode($schedule);
            }
        });

        static::updated(function (Schedule $schedule) {
            \Illuminate\Support\Facades\Log::info("Schedule updated event: " . $schedule->id);
            // If day changed from/to today, handle accordingly
            if ($schedule->wasChanged('day')) {
                if ($schedule->day === now()->format('l')) {
                    static::createVerificationCode($schedule);
                } else {
                    // Was today, now not (or distinct day), delete old codes linked to this schedule
                    // Note: We only delete FUTURE or TODAY's codes. Historic ones should theoretically stay? 
                    // But current system only tracks "date" = today mostly. 
                    // Let's just delete codes for today linked to this schedule.
                    VerificationCode::where('schedule_id', $schedule->id)
                        ->where('date', now()->toDateString())
                        ->delete();
                }
            } elseif ($schedule->day === now()->format('l')) {
                // Same day (today), ensure code exists
                static::createVerificationCode($schedule);
            }
        });

        static::deleted(function (Schedule $schedule) {
            VerificationCode::where('schedule_id', $schedule->id)->delete();
        });
    }

    protected static function createVerificationCode(Schedule $schedule): void
    {
        \Illuminate\Support\Facades\Log::info("Attempting to create code for Schedule: " . $schedule->id);
        // Check if exists
        $exists = VerificationCode::where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->exists();
        
        if ($exists) {
            static::updateVerificationCode($schedule);
            return;
        }

        // Skip if today is a holiday
        $isHoliday = Holiday::where('date', now()->toDateString())->exists();
        if ($isHoliday) {
            \Illuminate\Support\Facades\Log::info("Today is a holiday. Skipping code generation.");
            return;
        }

        // Ensure division allows auto-generate
        if ($schedule->division->is_auto_generate) {
             \Illuminate\Support\Facades\Log::info("Division allows auto-generate. Creating code.");
            VerificationCode::create([
                'division_id' => $schedule->division_id,
                'schedule_id' => $schedule->id,
                'code' => sprintf("%06d", mt_rand(1, 999999)),
                'date' => now()->toDateString(),
                'start_at' => now()->setTimeFromTimeString($schedule->start_time),
                'expires_at' => now()->setTimeFromTimeString($schedule->end_time),
                'is_active' => true,
            ]);
        } else {
             \Illuminate\Support\Facades\Log::info("Division does NOT allow auto-generate.");
        }
    }

    protected static function updateVerificationCode(Schedule $schedule): void
    {
        VerificationCode::where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->update([
                'division_id' => $schedule->division_id, // in case division changed
                'start_at' => now()->setTimeFromTimeString($schedule->start_time),
                'expires_at' => now()->setTimeFromTimeString($schedule->end_time),
            ]);
    }
}
