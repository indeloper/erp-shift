<?php

return [
    'mode' => 'utf-8',
    'format' => 'A4',
    'author' => '',
    'subject' => '',
    'keywords' => '',
    'creator' => 'Laravel Pdf',
    'display_mode' => 'fullpage',
    'tempDir' => public_path('temp'),
    'font_path' => base_path('resources/assets/fonts/'),
    'font_data' => [
        'calibri' => [
            'R' => 'calibri.ttf',    // regular font
            'B' => 'calibrib.ttf',       // optional: bold font
            'I' => 'calibrii.ttf',     // optional: italic font
            'BI' => 'calibriz.ttf', // optional: bold-italic font
        ],

    ],
];
