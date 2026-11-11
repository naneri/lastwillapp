<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/paddle',
        ]);

        // Local dev runs behind a tunnel (cloudflared) that terminates TLS
        // and forwards plain http to this app — without this, Laravel has
        // no way to know the original request was https, so every
        // absolute URL it generates (route(), url(), form actions, email
        // links) comes out as http:// and gets blocked or flagged as mixed
        // content by the browser. The tunnel's edge IP isn't fixed, so we
        // trust by header rather than by IP, same as the Laravel docs'
        // recommendation for Heroku/other proxying PaaS setups.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
