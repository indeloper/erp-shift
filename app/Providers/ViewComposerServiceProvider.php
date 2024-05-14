<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(
            [
                'layouts.app',
                'layouts.messages',
            ],
            \App\Http\ViewComposers\TaskComposer::class
        );

        View::composer(
            'building.material_accounting.modules.*', \App\Http\ViewComposers\MatAccComposer::class
        );

        View::composer(
            [
                'building.material_accounting.arrival.*',
                'building.material_accounting.moving.*',
                'building.material_accounting.transformation.*',
                'building.material_accounting.write_off.*',
            ],
            \App\Http\ViewComposers\ManualMaterialCategoryComposer::class
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
