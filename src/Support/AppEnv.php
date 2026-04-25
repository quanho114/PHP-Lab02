<?php

namespace TrainingApp\Support;

class AppEnv
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }
}
