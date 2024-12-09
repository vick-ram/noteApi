<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware as FoundationMiddleware;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\LogRequests;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (FoundationMiddleware $middleware) {
        // $middleware->append(JwtAuthMiddleware::class);
        $middleware->append(LogRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
