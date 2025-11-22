<?php

namespace System\CLI;

use System\Database\MigrationRunner;
use System\Cache\Cache;
use System\Queue\QueueWorker;
use System\Scheduler\Scheduler;

/**
 * CLI Kernel Handler
 */
class Command
{
    public static function handle(array $argv): void
    {
        $cmd = $argv[1] ?? null;
        $arg = $argv[2] ?? null;

        switch ($cmd) {
            case 'make:controller':
                Makers::makeController($arg);
                break;

            case 'make:model':
                Makers::makeModel($arg);
                break;

            case 'make:migration':
                Makers::makeMigration($arg);
                break;

            case 'migrate':
                MigrationRunner::run();
                break;

            case 'queue:work':
                QueueWorker::start();
                break;

            case 'schedule:run':
                Scheduler::runAll();
                break;

            case 'cache:clear':
                Cache::clearAll();
                break;

            default:
                echo "Unknown command.\n";
        }
    }
}
