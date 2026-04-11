<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetFilamentLocale;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ])
            ->brandName(fn () => site_setting(app()->getLocale() === 'ar' ? 'site_name_ar' : 'site_name_en', 'Hassan Almalki'))
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->darkMode(true)
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => [
                    50 => '#FAFAFA',
                    100 => '#F5F5F5',
                    200 => '#E0E0E0',
                    300 => '#BDBDBD',
                    400 => '#757575',
                    500 => '#424242',
                    600 => '#303030',
                    700 => '#212121',
                    800 => '#121212',
                    900 => '#000000',
                    950 => '#000000',
                ],
                'gray' => Color::Zinc,
                'danger' => Color::Red,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? 'المحتوى' : 'Content'),
                NavigationGroup::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الأخبار الآلية' : 'Auto News'),
                NavigationGroup::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? 'النظام' : 'System'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('@include("filament.language-switcher")')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<script src="https://kit.fontawesome.com/d64cd9d612.js" crossorigin="anonymous" defer></script>'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetFilamentLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
