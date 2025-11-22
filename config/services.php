<?php 


return [
    /*
    |--------------------------------------------------------------------------
    | Queue System
    |--------------------------------------------------------------------------
    | Define your queue driver and default queue.
    */
    'queue' => [
        'driver' => 'sync', // sync, redis, database, rabbitmq
        'default' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler / Cron Jobs
    |--------------------------------------------------------------------------
    | Add scheduled tasks. Format: ['command' => 'ClearCache', 'cron' => '* * * * *']
    */
    'scheduler' => [
        'enabled' => true,
        'tasks' => [
            // Example:
            // ['command' => \App\Commands\ClearCache::class, 'cron' => '0 * * * *'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    | Register all service providers to load during bootstrap.
    */
    'providers' => [
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        // Add more providers here
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom CLI Commands
    |--------------------------------------------------------------------------
    | Register all custom CLI commands to be available via `php cli`
    */
    'commands' => [
        \System\CLI\Makers::class,
        // Add more commands here
    ],

    /*
    |--------------------------------------------------------------------------
    | Future Services
    |--------------------------------------------------------------------------
    | You can add additional modules like:
    | - Events / Listeners
    | - Notifications
    | - Mail / SMS providers
    */
];