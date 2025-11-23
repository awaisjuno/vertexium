<?php

return [

    // -------------------------------------------------------
    // Application Settings
    // -------------------------------------------------------
    'name' => 'Vertexium',
    'env' => 'local',
    'debug' => true,
    'base_url' => 'http://localhost/framework',

    // -------------------------------------------------------
    // Database Connections
    // -------------------------------------------------------
    'default' => 'mysql',

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'vertexium',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],

    // -------------------------------------------------------
    // Redis
    // -------------------------------------------------------
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'database' => 0,
    ],

    // -------------------------------------------------------
    // File Storage
    // -------------------------------------------------------
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
