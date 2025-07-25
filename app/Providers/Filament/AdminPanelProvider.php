<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->spa()
            ->topNavigation()
            ->id('admin')
            ->path('')
            ->login()
            ->registration()
            ->passwordReset()
            ->revealablePasswords()
            ->profile(isSimple: false)
            // ->emailVerification()
            ->colors([
                'primary' => '#007a37',
            ])
            ->brandLogo('/storage/assets/feudlogo.png')
            ->brandLogoHeight('3.5rem')
            ->maxContentWidth(MaxWidth::SevenExtraLarge)
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //
            ])
            ->navigationItems([
                NavigationItem::make('test')
                    ->label('Test')
                    // ->url(fn(): string => route('filament.admin.pages.test'))
                    ->icon('heroicon-o-calendar')
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Schedule')
                    ->url(fn(): string => route('filament.admin.pages.schedule'))
                    ->visible(fn(): bool => auth()->user()->isFaculty())
                    ->icon('heroicon-o-calendar'),
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
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
            ]);
    }
}
