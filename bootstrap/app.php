<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \niklasravnsborg\LaravelPdf\PdfServiceProvider::class,
        \Intervention\Image\ImageServiceProvider::class,
        \Telegram\Bot\Laravel\TelegramServiceProvider::class,
        \Lexx\ChatMessenger\ChatMessengerServiceProvider::class,
        App\Providers\PHPExcelMacroServiceProvider::class, // Add this provider to the list,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->validateCsrfTokens(except: [
            '/login',
        ]);

        $middleware->append(\App\Http\Middleware\TrimStringsLimited::class);

        $middleware->throttleApi();

        $middleware->alias([
            'activeuser' => \App\Http\Middleware\ActiveUser::class,
            'log.requests' => \App\Http\Middleware\LogRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
