<?php
namespace System\Core;

class Config
{
    protected static array $items = [];

    /**
     * Load config file once
     */
    protected static function loadFile(string $name): void
    {
        if (!isset(self::$items[$name])) {
            $file = __DIR__ . "/../../config/{$name}.php";

            if (!file_exists($file)) {
                throw new \Exception("File not found");
            }

            self::$items[$name] = require $file;
        }
    }

    /**
     * Get config value (dot notation)
     */
    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $name = array_shift($segments);

        self::loadFile($name);

        $value = self::$items[$name] ?? $default;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}
