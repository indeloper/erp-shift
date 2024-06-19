<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            \URL::forceScheme('https');
        };

        if (config('app.env') != 'production') {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql.' ['.implode(', ', $query->bindings).']'.PHP_EOL
                );
            });
        }

        \Illuminate\Pagination\Paginator::useBootstrap();

        setlocale(LC_TIME, 'ru_RU.UTF-8');

        Carbon::setLocale('ru');

        Relation::morphMap([
            'regular' => \App\Models\Manual\ManualMaterial::class,
            'complect' => \App\Models\WorkVolume\WorkVolumeMaterialComplect::class,
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

        $this->bootAuth();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale('ru');
    }

    public function bootAuth(): void
    {
        //        Passport::routes();

        //allow everything for super admin
        Gate::before(function ($user, $ability, $arguments) {
            if ($user->is_su) {
                return true;
            }
            if (isset($arguments[0])) {
                $short_name = (new \ReflectionClass($arguments[0]))->getShortName();
                $permission = $ability.'.'.$short_name;
                $is_authed = $user->hasPermission($permission);
                if ($is_authed) {
                    return true;
                }
            }
        });

        // load check user permission
        foreach (Permission::all() as $permission) {
            $ability = $permission->codename;
            Gate::define($ability, function ($user) use ($ability) {
                return $user->hasPermission($ability);
            });
        }

    }
}
