<?php
declare(strict_types=1);

/**
 * Settings come from environment variables, or from an optional config.php
 * (copy config.example.php) that returns an array. Environment wins.
 */
final class Env
{
    private static ?array $file = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (self::$file === null) {
            $path = dirname(__DIR__) . '/config.php';
            $loaded = is_file($path) ? require $path : [];
            self::$file = is_array($loaded) ? $loaded : [];
        }
        if (isset(self::$file[$key]) && self::$file[$key] !== '') {
            return (string) self::$file[$key];
        }
        return $default;
    }

    public static function flag(string $key, bool $default): bool
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default): int
    {
        $value = self::get($key);
        return $value !== null && ctype_digit($value) ? (int) $value : $default;
    }
}
