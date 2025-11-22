<?php

namespace System\Core;

/**
 * Main Router Handler
 */
class Router
{
    public function dispatch()
    {
        $path = $_GET['url'] ?? '/';

        if ($path === '/') {
            $controller = new \App\Controllers\HomeController();
            return $controller->index();
        }

        echo "404 Not Found";
    }
}
