<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'notifications_dumping' => env('NOTIFICATION_DUMPING', false),

    'notification_dumping' => env('NOTIFICATION_DUMPING', false),

    'providers' => ServiceProvider::defaultProviders()->merge([
        //        Fomvasss\Dadata\DadataServiceProvider::class,
        niklasravnsborg\LaravelPdf\PdfServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\ViewComposerServiceProvider::class,
        Telegram\Bot\Laravel\TelegramServiceProvider::class,
        Lexx\ChatMessenger\ChatMessengerServiceProvider::class,
        App\Providers\PHPExcelMacroServiceProvider::class, // Add this provider to the list

        /*
         * Our Providers
         */
        \App\Providers\ObserversServiceProvider::class,
        \App\Providers\BladeDirectivesProvider::class,
        \App\Providers\BlueprintMacroServiceProvider::class,
        \App\Providers\ServiceServiceProvider::class,
        \App\Providers\RepositoryServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Image' => Intervention\Image\Facades\Image::class,
        'ManualMaterial' => \App\Models\Manual\ManualMaterial::class,
        'ManualMaterialParameter' => \App\Models\Manual\ManualMaterialParameter::class,
        'ManualReference' => \App\Models\Manual\ManualReference::class,
        'ManualReferenceParameter' => \App\Models\Manual\ManualReferenceParameter::class,
        'PDF' => niklasravnsborg\LaravelPdf\Facades\Pdf::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Telegram' => Telegram\Bot\Laravel\Facades\Telegram::class,
    ])->toArray(),

];
