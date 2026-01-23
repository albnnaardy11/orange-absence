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
            \App\Http\Responses\LogoutResponse::class
        );

        // Dynamic Livewire Component Failure Recovery
        // This scans the app/Filament directory and forcefully registers all components
        // to bypass the failing auto-discovery on the server.
        if (file_exists(app_path('Filament'))) {
            $filesystem = new \Illuminate\Filesystem\Filesystem();
            $files = $filesystem->allFiles(app_path('Filament'));

            foreach ($files as $file) {
                if (!str_ends_with($file->getFilename(), '.php')) continue;

                $relativePath = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname()
                );
                $class = 'App\\Filament\\' . $relativePath;

                if (class_exists($class) && is_subclass_of($class, \Livewire\Component::class) && !(new \ReflectionClass($class))->isAbstract()) {
                    $alias = \Illuminate\Support\Str::of($class)
                        ->replace('\\', '.')
                        ->explode('.')
                        ->map(fn ($segment) => \Illuminate\Support\Str::kebab($segment))
                        ->implode('.');
                    
                    \Livewire\Livewire::component($alias, $class);
                }
            }
        }
        
        // Ensure Vendor Login is also mapped
        \Livewire\Livewire::component('filament.auth.pages.login', \Filament\Auth\Pages\Login::class);
        
        // Ensure Database Notifications is mapped
        \Livewire\Livewire::component('filament.livewire.database-notifications', \Filament\Notifications\Livewire\DatabaseNotifications::class);
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
