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

        return Schedule::whereIn('division_id', $divisionIds)
            ->where('status', 'active')
            ->where('day', now()->format('l'))
            ->where('start_time', '<=', now()->format('H:i:s'))
            ->where('end_time', '>=', now()->format('H:i:s'))
            ->first();
    }
}
