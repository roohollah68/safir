<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => public_path('receipt'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'comment' => [
            'driver' => 'local',
            'root' => public_path('comment'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'deposit' => [
            'driver' => 'local',
            'root' => public_path('deposit'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'receipt' => [
            'driver' => 'local',
            'root' => public_path('receipt'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'p-photo' => [
            'driver' => 'local',
            'root' => public_path('product_photo'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'report' => [
            'driver' => 'local',
            'root' => public_path('report'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'withdrawal' => [
            'driver' => 'local',
            'root' => public_path('withdrawal'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'project' => [
            'driver' => 'local',
            'root' => public_path('project'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

        'process' => [
            'driver' => 'local',
            'root' => public_path('process'),
            'url' => env('APP_URL').'',
            'visibility' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
