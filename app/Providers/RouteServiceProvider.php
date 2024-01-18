<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::macro('registerBaseRoutes', function($controller, $attachmentsRoutes = false) {
            Route::get('/', $controller.'@getPageCore')->name('getPageCore');
            Route::apiResource('resource', $controller);
            if($attachmentsRoutes) {
                Route::post('uploadFile', $controller.'@uploadFile')->name('uploadFile');
                Route::post('downloadAttachments', $controller.'@downloadAttachments')->name('downloadAttachments');
            }
        });

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')->namespace(($this->namespace))->group(base_path('routes/web.php'));

        Route::middleware(['web','activeuser', 'auth'])->namespace($this->namespace)->group(function() {

                Route::prefix('contractors')->as('contractors::')->namespace('Commerce')->group(function () {
                    require base_path('routes/modules/contractors.php');
                });

                Route::prefix('building')->as('building::')->namespace('Building')->group(function() {
                    Route::prefix('mat_acc')->as('mat_acc::')->namespace('MaterialAccounting')->group(function() {
                        Route::prefix('arrival')->as('arrival::')->group(function() {
                            require base_path('routes/modules/building/material_accounting/arrival.php');
                        });
                        Route::prefix('write_off')->as('write_off::')->group(function() {
                            require base_path('routes/modules/building/material_accounting/write_off.php');
                        });
                        Route::prefix('transformation')->as('transformation::')->group(function() {
                            require base_path('routes/modules/building/material_accounting/transformation.php');
                        });
                        Route::prefix('moving')->as('moving::')->group(function() {
                            require base_path('routes/modules/building/material_accounting/moving.php');
                        });

                        require base_path('routes/modules/building/material_accounting/main.php');
                    });

                    Route::prefix('tech_acc')->as('tech_acc::')->namespace('TechAccounting')->group(function() {
                        require base_path('routes/modules/building/tech_accounting/tech.php');
                    });

                    Route::prefix('vehicles')->as('vehicles::')->namespace('TechAccounting')->group(function() {
                        require base_path('routes/modules/building/tech_accounting/vehicles.php');
                    });
                });

                Route::namespace('Commerce')->prefix('projects')->as('projects::')->group(function() {
                    require base_path('routes/modules/projects.php');
                });

                Route::namespace('System')->prefix('messages')->as('messages::')->group(function() {
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
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
