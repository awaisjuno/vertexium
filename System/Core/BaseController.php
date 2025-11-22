<?php
namespace System\Core;

class BaseController
{
    /**
     * Load a view
     *
     * @param string $view The name of the view file in App/Views
     * @param array $data Optional data to pass to the view
     * @return void
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        // Use DIRECTORY_SEPARATOR to be OS-independent
        $viewFile = realpath(__DIR__ . '/../../App/Views/' . $view . '.php');

        if ($viewFile && file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View file '{$view}.php' not found! Path tried: " . __DIR__ . '/../../../App/Views/' . $view . '.php';
            echo "<br>Realpath: " . var_export($viewFile, true);
        }
    }
}
