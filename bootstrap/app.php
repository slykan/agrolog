<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\ZabiljeziAktivnost::class);
        // savjetnik u klijentovom gospodarstvu (mora biti nakon ZabiljeziAktivnost: aktivnost se bilježi savjetniku)
        $middleware->appendToGroup('web', \App\Http\Middleware\GospodarstvoSavjetnika::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
