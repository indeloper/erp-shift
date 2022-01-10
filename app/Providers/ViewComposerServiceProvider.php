<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
                'layouts.messages'
            ],
            'App\Http\ViewComposers\TaskComposer'
        );

        View::composer(
            'building.material_accounting.modules.*', 'App\Http\ViewComposers\MatAccComposer'
        );

        View::composer(
            [
                'building.material_accounting.arrival.*',
                'building.material_accounting.moving.*',
                'building.material_accounting.transformation.*',
                'building.material_accounting.write_off.*'
            ],
            'App\Http\ViewComposers\ManualMaterialCategoryComposer'
        );

        View::composer(
            [
                'human_resources.reports.summary_report',
                'human_resources.reports.daily_report',
                'human_resources.reports.detailed_report',
            ],
            'App\Http\ViewComposers\TimecardComposer'
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
