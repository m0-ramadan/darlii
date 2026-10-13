<?php
use App\Http\Middleware\ApplyVisitorLocale;
use App\Http\Middleware\EnsureAdminPanel;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SetCurrency;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShowMaintenancePage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [ApplyVisitorLocale::class, ShowMaintenancePage::class]);

        $middleware->redirectTo(
            guests: fn (\Illuminate\Http\Request $request) => $request->is('admin/*') || $request->is('admin')
                ? route('admin.login')
                : '/' . ($request->route('locale') ?? config('locales.default', 'ar')) . '/login',
            users: fn (\Illuminate\Http\Request $request) => ($request->user() && $request->user()->hasAnyRole(\App\Support\PermissionCatalog::panelRoles()))
                ? '/admin'
                : '/' . ($request->route('locale') ?? config('locales.default', 'ar')) . '/dashboard/properties',
        );

        $middleware->alias([
            'set.locale' => SetLocale::class,
            'set.currency' => SetCurrency::class,
            'role' => EnsureRole::class,
            'admin.panel' => EnsureAdminPanel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Central exception customization can be added here.
    })->create();
