<?php

use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\LogRequests;
use App\Models\User;
use App\Notifications\Exceptions\ExceptionNotice;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageServiceProvider;
use Lexx\ChatMessenger\ChatMessengerServiceProvider;
use niklasravnsborg\LaravelPdf\PdfServiceProvider;
use Telegram\Bot\Laravel\TelegramServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        PdfServiceProvider::class,
        ImageServiceProvider::class,
        TelegramServiceProvider::class,
        ChatMessengerServiceProvider::class,
        App\Providers\PHPExcelMacroServiceProvider::class,
        // Add this provider to the list,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn() => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->validateCsrfTokens(except: [
            '/login',
        ]);

        $middleware->append(\App\Http\Middleware\TrimStringsLimited::class);

        $middleware->throttleApi();

        $middleware->alias([
            'activeuser'   => ActiveUser::class,
            'log.requests' => LogRequests::class,
        ]);
    })
    ->withExceptions(using: function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            if (config('app.env') !== 'local')
            {
                ExceptionNotice::send(
                    User::where('is_su', 1)->get()->pluck('id')->toArray(),
                    [
                        'name'             => ExceptionNotice::DESCRIPTION,
                        'exceptionMessage' => $e->getMessage(),
                        'user'             => Auth::user(),
                        'ip'               => request()?->ip(),
                    ]
                );
            }
        });
    })->create();
