<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Observers\AttendanceObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Auth\Events\Login;
use App\Listeners\UserLoginListener;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Attendance::observe(AttendanceObserver::class);

        // Track IP and Device in Activity Log
        LogBatch::startBatch();
        Activity::creating(function (Activity $activity) {
            $activity->ip_address = request()->ip();
            $activity->user_agent = request()->userAgent();
        });

        // Register Login Listener
        Event::listen(Login::class, UserLoginListener::class);
    }
}
