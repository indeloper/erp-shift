<?php

return [
    /*
    |
    |--------------------------------------------------------------------------
    | Flare API key
    |--------------------------------------------------------------------------
    |
    | Specify Flare's API key below to enable error reporting to the service.
    |
    | More info: https://flareapp.io/docs/general/projects
    |
    */

    'key' => env('FLARE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Reporting Options
    |--------------------------------------------------------------------------
    |
    | These options determine which information will be transmitted to Flare.
    |
    */

    'reporting' => [
        'anonymize_ips' => false,
        'collect_git_information' => false,
        'report_queries' => false,
        'maximum_number_of_collected_queries' => 200,
        'report_query_bindings' => false,
        'report_view_data' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Log statements
    |--------------------------------------------------------------------------
    |
    | If this setting is `false` log statements won't be send as events to Flare,
    | no matter which error level you specified in the Flare log channel.
    |
    */

    'send_logs_as_events' => false,
];
