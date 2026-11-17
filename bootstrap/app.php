<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        if (getenv('APP_ENV') === 'production') {
            $middleware->trustProxies(at: '*');
        }

        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => route('login') . '?next=' . urlencode($request->getRequestUri()));
        $middleware->redirectUsersTo(function () {
            return auth()->check() ? home_for_role(auth()->user()->role) : '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
