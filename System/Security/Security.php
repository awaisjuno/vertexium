<?php

namespace System\Security;

/**
 * Class Security
 *
 * Provides input sanitization, URL parameter filtering,
 * XSS protection, SQL injection prevention, and type validation.
 *
 * @package System\Security
 */
class Security
{
    /**
     * Sanitize a string (for HTML output, URLs, logs, etc.)
     *
     * @param string $input
     * @return string
     */
    public static function cleanString(string $input): string
    {
        $input = trim($input);

        // Remove invisible ASCII chars
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);

        // Remove harmful characters
        $input = preg_replace('/[^a-zA-Z0-9_\-@.,\s]/u', '', $input);

        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean a URL parameter to prevent RCE, LFI, path traversal, etc.
     *
     * @param string $value
     * @return string
     */
    public static function cleanUrlParam(string $value): string
    {
        // Remove ../  ./   // etc.
        $value = str_replace(
            ['../', './', '//', '..\\', '.\\', '\\\\'],
            '',
            $value
        );

        // Allow only safe characters
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $value);
    }

    /**
     * Sanitize POST/GET input recursively
     *
     * @param array|string $data
     * @return array|string
     */
    public static function cleanInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }

        return self::cleanString($data);
    }

    /**
     * Protect against XSS
     *
     * @param string $input
     * @return string
     */
    public static function xssProtect(string $input): string
    {
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if input contains SQL injection pattern
     *
     * @param string $input
     * @return bool
     */
    public static function hasSqlInjection(string $input): bool
    {
        $patterns = [
            '/(\bUNION\b)/i',
            '/(\bSELECT\b)/i',
            '/(\bDROP\b)/i',
            '/(\bINSERT\b)/i',
            '/(\bUPDATE\b)/i',
            '/(\bDELETE\b)/i',
            '/(\bWHERE\b)/i',
            '/(--)/',
            '/(#)/',
            '/(;)/',
            '/(\bOR\b)/i',
            '/(\bAND\b)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate URL parameters safely
     *
     * @param string $value
     * @return string|null
     */
    public static function validateUrlParam(string $value): ?string
    {
        $value = self::cleanUrlParam($value);

        if (self::hasSqlInjection($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Global protection for $_GET and $_POST
     *
     * @return void
     */
    public static function applyGlobalProtection(): void
    {
        $_GET  = self::cleanInput($_GET);
        $_POST = self::cleanInput($_POST);
    }
}
