<?php

namespace System\CLI;

/**
 * Code generator for controllers, models, migrations
 */
class Makers
{
    public static function makeController(string $name): void
    {
        $path = "App/Controllers/{$name}.php";

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $template = <<<PHP
<?php

namespace App\Controllers;

use System\Core\BaseController;

/**
 * Controller: {$name}
 */
class {$name} extends BaseController
{
    public function index()
    {
        echo "{$name} controller loaded";
    }
}

PHP;

        file_put_contents($path, $template);
        echo "Controller created: {$name}\n";
    }

    public static function makeModel(string $name): void
    {
        $path = "App/Models/{$name}.php";

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $template = <<<PHP
<?php

namespace App\Models;

use System\Database\DB;

/**
 * Model: {$name}
 */
class {$name} extends DB
{
    protected string \$table = '{$name}';
}

PHP;

        file_put_contents($path, $template);
        echo "Model created: {$name}\n";
    }

    public static function makeMigration(string $name): void
    {
        $time = date('Y_m_d_His');
        $file = "app/database/migrations/{$time}_{$name}.php";

        // Ensure the directory exists
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $template = <<<PHP
<?php

use System\Database\Migration;

return new class extends Migration
{
    public function up()
    {
        // TODO: migration logic
    }

    public function down()
    {
        // TODO: rollback logic
    }
};

PHP;

        file_put_contents($file, $template);
        echo "Migration created: {$file}\n";
    }
}
