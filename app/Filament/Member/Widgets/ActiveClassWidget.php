<?php

namespace App\Filament\Member\Widgets;

use Filament\Widgets\Widget;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class ActiveClassWidget extends Widget
{
    protected string $view = 'filament.member.widgets.active-class-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getActiveSchedule()
    {
        $user = Auth::user();
        if (!$user) return null;

        $divisionIds = $user->divisions()->pluck('divisions.id');

        $now = now()->format('H:i:s');
        $today = now()->format('l');
        $yesterday = now()->subDay()->format('l');

        return Schedule::whereIn('division_id', $divisionIds)
            ->where('status', 'active')
            ->where(function ($query) use ($today, $yesterday, $now) {
                // Case 1: Today schedule
                $query->where(function ($q) use ($today, $now) {
                    $q->where('day', $today)
                      ->where(function ($timeQ) use ($now) {
                          // Normal: start <= end AND start <= now <= end
                          $timeQ->where(function ($normal) use ($now) {
                              $normal->whereColumn('start_time', '<=', 'end_time')
                                     ->where('start_time', '<=', $now)
                                     ->where('end_time', '>=', $now);
                          })
                          // Overnight (started today, ends tomorrow): start > end AND now >= start
                          ->orWhere(function ($overnight) use ($now) {
                              $overnight->whereColumn('start_time', '>', 'end_time')
                                        ->where('start_time', '<=', $now);
                          });
                      });
                })
                // Case 2: Yesterday schedule (overnight, ends today)
                ->orWhere(function ($q) use ($yesterday, $now) {
                    $q->where('day', $yesterday)
                      ->whereColumn('start_time', '>', 'end_time') // Must be overnight
                      ->where('end_time', '>=', $now); // Still running (now <= end)
                });
            })
            ->first();
    }
}
