<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') != 'production') {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql.' ['.implode(', ', $query->bindings).']'.PHP_EOL
                );
            });
        }

        setlocale(LC_TIME, 'ru_RU.UTF-8');

        Carbon::setLocale('ru');

        Relation::morphMap([
            'regular' => 'App\Models\Manual\ManualMaterial',
            'complect' => 'App\Models\WorkVolume\WorkVolumeMaterialComplect',
        ]);

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param  int  $perPage
         * @param  int  $total
         * @param  int  $page
         * @param  string  $pageName
         * @return array
         */
        Collection::macro('collection_paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale('ru');
    }
}
