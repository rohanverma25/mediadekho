<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Uploaded files (logos, category/media images, resumes, ...) live
        // directly under public/uploads — not storage/app/public behind the
        // storage:link symlink Laravel normally uses. On this app's shared
        // host that symlink either can't be created (no SSH access) or
        // doesn't survive a zip-upload deploy, which was a repeated source
        // of broken image URLs. public/ is always directly web-reachable
        // with zero extra server config, so storing here sidesteps the
        // whole symlink problem entirely.
        'public' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            // ASSET_URL, when set, is an explicit override for wherever
            // uploaded files are actually reachable from — takes priority
            // over the APP_URL-derived guess below, and over
            // AppServiceProvider's per-request auto-detection (see
            // configurePublicDiskUrl()). Rarely needed now that files live
            // under public/, but kept as an escape hatch.
            'url' => env('ASSET_URL')
                ? rtrim(env('ASSET_URL'), '/')
                : rtrim(env('APP_URL', 'http://localhost'), '/').'/uploads',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
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
    | Empty — uploads live directly under public/uploads (see the 'public'
    | disk above), so there's no symlink to create.
    |
    */

    'links' => [],

];
