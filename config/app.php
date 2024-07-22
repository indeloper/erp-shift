<?php

use App\Facades\TGUserWebApp;
use Illuminate\Support\Facades\Facade;

return [

    'timezone' => 'Europe/Moscow',

    'notifications_dumping' => env('NOTIFICATION_DUMPING', false),

    'notification_dumping' => env('NOTIFICATION_DUMPING', false),

    'aliases' => Facade::defaultAliases()->merge([
        'Image'                    => Intervention\Image\Facades\Image::class,
        'ManualMaterial'           => \App\Models\Manual\ManualMaterial::class,
        'ManualMaterialParameter'  => \App\Models\Manual\ManualMaterialParameter::class,
        'ManualReference'          => \App\Models\Manual\ManualReference::class,
        'ManualReferenceParameter' => \App\Models\Manual\ManualReferenceParameter::class,
        'PDF'                      => niklasravnsborg\LaravelPdf\Facades\Pdf::class,
        'Redis'                    => Illuminate\Support\Facades\Redis::class,
        'Telegram'                 => Telegram\Bot\Laravel\Facades\Telegram::class,
        'TGUserWebApp'             => TGUserWebApp::class,
    ])->toArray(),

];
