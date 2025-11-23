<?php

namespace System\CLI;

use System\Database\Migration\MigrationRunner;
//use System\Cache\Cache;
//use System\Queue\QueueWorker;
//use System\Scheduler\Scheduler;

/**
 * CLI Kernel Handler
 */
class Command
{
    public static function handle(array $argv): void
    {
        $command = $argv[0] ?? null;  // first argument after 'cli'
        $arg     = $argv[1] ?? null;  // optional argument (name)

        if (!$command) {
            self::help();
            exit;
        }

        switch ($command) {

            // ---------------------
            // Makers
            // ---------------------
            case 'make:controller':
                if (!$arg) {
                    echo "Please provide controller name: ";
                    $arg = trim(fgets(STDIN));
                }
                Makers::makeController($arg);
                break;

            case 'make:model':
                if (!$arg) {
                    echo "Please provide model name: ";
                    $arg = trim(fgets(STDIN));
                }
                Makers::makeModel($arg);
                break;

            case 'make:migration':
                if (!$arg) {
                    echo "Please provide migration name: ";
                    $arg = trim(fgets(STDIN));
                }
                Makers::makeMigration($arg);
                break;

            // ---------------------
            // Migrations
            // ---------------------
            case 'migrate':
                MigrationRunner::run();
                break;

            case 'migrate:rollback':
                MigrationRunner::rollback();
                break;

            case 'migrate:specific':
                if (!$arg) {
                    echo "❌ Please provide migration name.\n";
                    exit;
                }
                MigrationRunner::runSpecific($arg);
                break;

            // ---------------------
            // Queue Worker
            // ---------------------
            case 'queue:work':
                QueueWorker::start();
                break;

            // ---------------------
            // Scheduler
            // ---------------------
            case 'schedule:run':
                Scheduler::runAll();
                break;

            // ---------------------
            // Cache
            // ---------------------
            case 'cache:clear':
                Cache::clearAll();
                break;

            // ---------------------
            // Help / default
            // ---------------------
            case 'help':
            default:
                self::help();
                break;
        }
    }

    /**
     * Display list of available commands
     */
    public static function help(): void
    {
        echo "Available Commands:\n";
        echo "-------------------------------------\n";
        echo "  make:controller     ControllerName\n";
        echo "  make:model          ModelName\n";
        echo "  make:migration      MigrationName\n\n";

        echo "  migrate             Run all pending migrations\n";
        echo "  migrate:rollback    Roll back last batch\n";
        echo "  migrate:specific    MigrationName\n\n";

        echo "  queue:work          Start queue worker\n";
        echo "  schedule:run        Execute all scheduled tasks\n";
        echo "  cache:clear         Clear framework cache\n";
        echo "-------------------------------------\n";
    }
}
