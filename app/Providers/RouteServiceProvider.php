<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Route::macro('registerBaseRoutes', function ($controller, $attachmentsRoutes = false) {
            Route::get('/', $controller.'@getPageCore')->name('getPageCore');
            Route::apiResource('resource', $controller);
            if ($attachmentsRoutes) {
                Route::post('uploadFile', $controller.'@uploadFile')->name('uploadFile');
                Route::post('downloadAttachments', $controller.'@downloadAttachments')->name('downloadAttachments');
            }
        });

        parent::boot();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        // Подключаем маршруты для шаблона
        $this->mapLayoutRoutes();
        $this->mapProfileRoutes();
        $this->mapNotificationsRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(base_path('routes/web.php'));

        Route::middleware(['web', 'activeuser', 'auth'])->group(function () {

            Route::prefix('contractors')->as('contractors::')->group(function () {
                require base_path('routes/modules/contractors.php');
            });

            Route::prefix('building')->as('building::')->group(function () {
                Route::prefix('mat_acc')->as('mat_acc::')->group(function () {
                    Route::prefix('arrival')->as('arrival::')->group(function () {
                        require base_path('routes/modules/building/material_accounting/arrival.php');
                    });
                    Route::prefix('write_off')->as('write_off::')->group(function () {
                        require base_path('routes/modules/building/material_accounting/write_off.php');
                    });
                    Route::prefix('transformation')->as('transformation::')->group(function () {
                        require base_path('routes/modules/building/material_accounting/transformation.php');
                    });
                    Route::prefix('moving')->as('moving::')->group(function () {
                        require base_path('routes/modules/building/material_accounting/moving.php');
                    });

                    require base_path('routes/modules/building/material_accounting/main.php');
                });

                Route::prefix('tech_acc')->as('tech_acc::')->group(function () {
                    require base_path('routes/modules/building/tech_accounting/tech.php');
                });

                Route::prefix('vehicles')->as('vehicles::')->group(function () {
                    require base_path('routes/modules/building/tech_accounting/vehicles.php');
                });
            });

            Route::prefix('projects')->as('projects::')->group(function () {
                require base_path('routes/modules/projects.php');
            });

            Route::prefix('messages')->as('messages::')->group(function () {
                require base_path('routes/modules/messages.php');
            });
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    private function mapLayoutRoutes()
    {
        Route::middleware(['web', 'auth'])
            ->prefix('layout')
            ->name('layout::')
            ->group(base_path('routes/layout/layout.php'));
    }

    private function mapProfileRoutes()
    {
        Route::middleware(['web', 'auth'])
            ->prefix('profile')
            ->name('profile::')
            ->group(base_path('routes/user/profile.php'));
    }

    private function mapNotificationsRoutes()
    {
        Route::middleware(['web', 'auth', 'activeuser'])
            ->group(base_path('routes/notifications/notifications.php'));
    }
}
