<?php

namespace System\Core;

use System\Core\Router;

/**
 * Main Application Kernel (PSR-4)
 */
class App
{
    public function run()
    {
        session_start();

        $router = new Router();
        $router->dispatch();
    }
}
