<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeDirectivesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        Blade::directive('user', function ($userId) {
            $user = User::findOrFail($userId);

            return '<a href='.route('users::card', $user->id).' class="activity-content__link">'.
                $user->long_full_name.'</a>';
        });
    }
}
