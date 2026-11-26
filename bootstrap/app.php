<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::group([], base_path('routes/test.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\AddAppInstanceHeader::class);
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
        // Cek role sebelum route model binding, agar akun tanpa hak tidak bisa menebak ID (403, bukan 404).
        $middleware->prependToPriorityList(\Illuminate\Routing\Middleware\SubstituteBindings::class, \App\Http\Middleware\EnsureRole::class);
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
