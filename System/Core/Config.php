<?php
namespace System\Core;

class Config
{
    protected static array $items = [];

    /**
     * Load config file once
     */
    public static function load(string $key): void
    {
        if (!isset(self::$items[$key])) {
            $file = __DIR__ . "/../../config/{$key}.php";
            if (file_exists($file)) {
                self::$items[$key] = require $file;
            } else {
                throw new \Exception("Config file {$key}.php not found.");
            }
        }
    }

    /**
     * Get a config value using dot notation
     */
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $first = array_shift($parts);

        self::load($first);

        $value = self::$items[$first] ?? $default;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }

        return $value;
    }
}
