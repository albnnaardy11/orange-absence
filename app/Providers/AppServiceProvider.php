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
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );

        // Force register Filament Login component to fix resolution issues
        \Livewire\Livewire::component('filament.auth.pages.login', \Filament\Auth\Pages\Login::class);
        
        // Force register LiveAttendance component
        \Livewire\Livewire::component('app.filament.pages.live-attendance', \App\Filament\Pages\LiveAttendance::class);
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);
        Attendance::observe(AttendanceObserver::class);
        \App\Models\Schedule::observe(\App\Observers\ScheduleObserver::class);

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
