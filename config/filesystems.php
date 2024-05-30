<?php

return [

    'disks' => [
        'user_images' => [
            'driver' => 'local',
            'root' => storage_path('app/public/img/user_images'),
            'url' => env('APP_URL').'/user_images',
            'visibility' => 'public',
        ],

        'project_documents' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/project_documents'),
            'url' => env('APP_URL').'/project_documents',
            'visibility' => 'private',
        ],

        'work_volume_request_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/work_volume_request_files'),
            'url' => env('APP_URL').'/work_volume_request_files',
            'visibility' => 'private',
        ],

        'commercial_offer_request_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/commercial_offer_request_files'),
            'url' => env('APP_URL').'/commercial_offer_request_files',
            'visibility' => 'private',
        ],

        'contract_request_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/contract_request_files'),
            'url' => env('APP_URL').'/contract_request_files',
            'visibility' => 'private',
        ],

        'contract_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/contract_files'),
            'url' => env('APP_URL').'/contract_files',
            'visibility' => 'private',
        ],

        'commercial_offers' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/commercial_offers'),
            'url' => env('APP_URL').'/commercial_offers',
            'visibility' => 'private',
        ],

        'technics' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/tech_accounting'),
            'url' => env('APP_URL').'/technics',
            'visibility' => 'private',
        ],

        'vehicles' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/vehicles'),
            'url' => env('APP_URL').'/vehicles',
            'visibility' => 'private',
        ],

        'defect_photos' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/defect_photos'),
            'url' => env('APP_URL').'/defect_photos',
            'visibility' => 'private',
        ],

        'defect_videos' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/defect_videos'),
            'url' => env('APP_URL').'/defect_videos',
            'visibility' => 'private',
        ],

        'budget' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/budget'),
            'url' => env('APP_URL').'/budget',
            'visibility' => 'private',
        ],

        'commercial_offers_contractor_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/commercial_offers_contractor_files'),
            'url' => env('APP_URL').'/commercial_offers_contractor_files',
            'visibility' => 'private',
        ],

        'contracts' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/contracts'),
            'url' => env('APP_URL').'/contracts',
            'visibility' => 'private',
        ],

        'task_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/task_files'),
            'url' => env('APP_URL').'/task_files',
            'visibility' => 'private',
        ],

        'material_passport' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/material_passport'),
            'url' => env('APP_URL').'/material_passport',
            'visibility' => 'private',
        ],

        'com_offer_gantt' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/gantt_com_offer'),
            'url' => env('APP_URL').'/gantt_com_offer',
            'visibility' => 'private',
        ],

        'support_mail_image' => [
            'driver' => 'local',
            'root' => storage_path('app/public/img/support_mail_images'),
            'url' => env('APP_URL').'/support_mail_images',
            'visibility' => 'public',
        ],

        'mat_acc_operation_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/mat_acc_operation_files'),
            'url' => env('APP_URL').'/mat_acc_operation_files',
            'visibility' => 'private',
        ],

        'material_operation_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/material_operation_files'),
            'url' => env('APP_URL').'/material_operation_files',
            'visibility' => 'private',
        ],

        'ttns' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/ttns'),
            'url' => env('APP_URL').'/ttns',
            'visibility' => 'private',
        ],

        'message_files' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/message_files'),
            'url' => env('APP_URL').'/message_files',
            'visibility' => 'private',
        ],

        'project_object_documents' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/project_object_documents'),
            'url' => env('APP_URL').'/project_object_documents',
            'visibility' => 'public',
        ],

        'technic' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/technic'),
            'url' => env('APP_URL').'/building/tech_acc/technic/ourTechnicList',
            'visibility' => 'private',
        ],

        'fuel_flow' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/fuel_flow'),
            'url' => env('APP_URL').'storage/docs/fuel_flow/',
            'visibility' => 'private',
        ],

        'zip_archives' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/zip_archives'),
            'url' => env('APP_URL').'/storage/docs/zip_archives/',
            'visibility' => 'private',
        ],

        'technic_movements' => [
            'driver' => 'local',
            'root' => storage_path('app/public/docs/technic_movements'),
            'url' => env('APP_URL').'storage/docs/technic_movements/',
            'visibility' => 'private',
        ],
    ],

];
