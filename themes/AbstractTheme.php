<?php
declare(strict_types=1);

/**
 * Fælles grundlag for temaer.
 *
 * Et tema behøver kun at skrive name(), blocks() og globals(). slug() læses
 * af mappen, klassen ligger i.
 */
abstract class AbstractTheme implements ThemeInterface
{
    final public static function slug(): string
    {
        $file = (new ReflectionClass(static::class))->getFileName();

        return $file === false ? '' : basename(dirname($file));
    }

    public static function description(): string
    {
        return '';
    }

    public static function thumbnail(): string
    {
        return '';
    }

    public static function sortOrder(): int
    {
        return 100;
    }

    public static function isReady(): bool
    {
        return true;
    }
}
