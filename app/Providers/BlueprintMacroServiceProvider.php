<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class BlueprintMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        Blueprint::macro('authorAndEditor', function () {
            $this->unsignedInteger('author_id')->comment('Идентификатор пользователя-автора записи');
            $this->unsignedInteger('editor_id')->comment('Идентификатор пользователя, который внес последние изменения в запись');

            $this->foreign('author_id')->references('id')->on('users');
            $this->foreign('editor_id')->references('id')->on('users');
        });

        Blueprint::macro('audit', function () {
            $this->authorAndEditor();
            $this->timestamps();
            $this->softDeletes();
        });
    }
}
