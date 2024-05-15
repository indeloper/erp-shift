<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();

            $this->mapWebRoutes();

            // Подключаем маршруты для шаблона
            $this->mapLayoutRoutes();
            $this->mapProfileRoutes();

            //
        });
Route::macro('registerBaseRoutes', function ($controller, $attachmentsRoutes = false) {
            Route::get('/', $controller.'@getPageCore')->name('getPageCore');
            Route::apiResource('resource', $controller);
            if ($attachmentsRoutes) {
                Route::post('uploadFile', $controller.'@uploadFile')->name('uploadFile');
                Route::post('downloadAttachments', $controller.'@downloadAttachments')->name('downloadAttachments');
            }
        });    }



    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web'))->group(base_path('routes/web.php'));

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
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    private function mapLayoutRoutes()
    {
        Route::middleware(['web', 'auth'])
            )
            ->prefix('layout')
            ->name('layout::')
            ->group(base_path('routes/layout/layout.php'));
    }

    private function mapProfileRoutes()
    {
        Route::middleware(['web', 'auth'])
            )
            ->prefix('profile')
            ->name('profile::')
            ->group(base_path('routes/user/profile.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
