<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;

class MemberPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('member')
            ->path('member')
            ->login()
            ->profile(\App\Filament\Member\Pages\Auth\EditProfile::class)

            ->brandName('ABSEN ORANGE')
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Absensi')
                     ->collapsible(),
                NavigationGroup::make()
                    ->label('Kegiatan')
                    ->collapsible(),
                NavigationGroup::make()
                    ->label('Keuangan')
                    ->collapsible(),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->viteTheme('resources/css/app.css')
            ->discoverResources(in: app_path('Filament/Member/Resources'), for: 'App\Filament\Member\Resources')
            ->discoverPages(in: app_path('Filament/Member/Pages'), for: 'App\Filament\Member\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Member/Widgets'), for: 'App\Filament\Member\Widgets')
            ->widgets([
                \App\Filament\Member\Widgets\MemberStatsWidget::class,
                \App\Filament\Member\Widgets\MemberAttendanceChart::class,
                \App\Filament\Member\Widgets\ActiveClassWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckSuspended::class,
            ])
            ->spa()
            ->sidebarCollapsibleOnDesktop(false) // or ->sidebar(false) if supported, but topNavigation disables sidebar usually
            ->breadcrumbs(false);
    }
}
