<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middleware\RoleMiddleware;

class MahasiswaPanelProvider extends PanelProvider
{
    public function auth(): void
    {
        Filament::auth(function () {
            return auth()->check() && auth()->user()->hasRole('mahasiswa');
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('mahasiswa')
            ->path('mahasiswa')
            ->login(false)
            ->renderHook(
                'panels::head.start',
                fn () => view('filament.favicon')
            )
            ->databaseNotifications()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Indigo,
                'primary' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Poppins')
            ->brandName('MAGO')
            ->brandLogo(asset('images/logo1.png'))
            ->brandLogoHeight('1.5rem')
            ->darkModeBrandLogo(asset('images/logo2.png'))
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Mahasiswa/Resources'), for: 'App\\Filament\\Mahasiswa\\Resources')
            ->discoverPages(in: app_path('Filament/Mahasiswa/Pages'), for: 'App\\Filament\\Mahasiswa\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Mahasiswa/Widgets'), for: 'App\\Filament\\Mahasiswa\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                \App\Filament\Mahasiswa\Widgets\MahasiswaStatusPengajuanTable::class,
                \App\Filament\Mahasiswa\Widgets\RekomendasiMagang::class,
                \App\Filament\Mahasiswa\Widgets\PreferensiWarning::class,
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
                RoleMiddleware::class.':mahasiswa',
            ]);
    }
}
