<?php

namespace App\Providers\Filament;

use App\Filament\Pages\TeacherProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PainelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // Identidade & rota base
            ->default()
            ->id('painel')
            ->path(' ')

            // Auth & verificação
            ->login()
            ->emailVerification()

            // Branding
            ->colors([
                'primary' => Color::hex('#7E1012'),
                'gray'    => Color::Zinc,
            ])
            ->font(
                'Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            )
            ->maxContentWidth(Width::Full)

            // Descoberta
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')

            // Páginas & widgets básicos
            ->pages([
                Dashboard::class, // ou sua Dashboard custom (recomendado, ver opção B)
            ])
            ->widgets([
                AccountWidget::class,
                // Removi o FilamentInfoWidget para limpar o dashboard
            ])
            ->profile(TeacherProfile::class) // sempre disponível

            // Middlewares globais do painel
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

            // Middlewares de auth
            ->authMiddleware([
                Authenticate::class,
                // \App\Http\Middleware\EnsureTeacherProfileComplete::class, // habilite se usar opção A
            ])

            // UX
            ->unsavedChangesAlerts();
    }
}
