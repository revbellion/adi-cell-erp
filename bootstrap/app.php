<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => CheckRole::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Endpoint API print-calc: tanpa CSRF (dilindungi token sendiri)
        $middleware->validateCsrfTokens(except: ['api/print-log']);

        $middleware->redirectUsersTo(function () {
            $user = Auth::user();
            if ($user && ($user->isAdmin() || $user->hasPermission('dashboard'))) {
                return route('dashboard');
            }
            return route('stock.sales');
        });

        // Force HTTPS di production
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
