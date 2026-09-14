<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // Disable CSRF for webhook route
        $middleware->validateCsrfTokens(except: [
            'api/tgt/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Handle expired / invalid encrypted session cookie gracefully on container restart
        $exceptions->render(function (\Illuminate\Contracts\Encryption\DecryptException $e, Request $request) {
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            return redirect()->route('login')->with('info', 'Oturum anahtarı yenilendi, lütfen tekrar giriş yapın.');
        });

        // Handle CSRF Token Mismatch gracefully
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            return redirect()->route('login')->with('error', 'Oturum süresi doldu, lütfen tekrar giriş yapın.');
        });
    })->create();
