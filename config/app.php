<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | Here you can define the basic settings for your framework.
    | 'env' can be 'local', 'production', 'staging'.
    | 'debug' enables detailed error messages.
    |
    */

    'name' => 'AwaisPHP',
    'env' => 'local',
    'debug' => true,
    'base_url' => 'http://localhost/framework',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Define your database connections here. Currently, only MySQL is active.
    | You can easily add PostgreSQL, SQLite, SQL Server later.
    |
    */

    'databases' => [
        'default' => 'mysql',
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'app_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],

        /*
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'app_pg',
            'username' => 'postgres',
            'password' => 'secret',
            'charset' => 'utf8',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/database.sqlite',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => '127.0.0.1',
            'port' => 1433,
            'database' => 'app_sqlsrv',
            'username' => 'sa',
            'password' => 'secret',
        ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Redis can be used for caching, queues, or session storage.
    |
    */
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'database' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    |
    | Define storage disks for file uploads and assets.
    |
    */
    'storage' => [
        'default' => 'local',
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => __DIR__ . '/../storage',
            ],
            'public' => [
                'driver' => 'local',
                'root' => __DIR__ . '/../public/uploads',
                'url' => '/uploads',
                'visibility' => 'public',
            ],
        ],
    ],
];
