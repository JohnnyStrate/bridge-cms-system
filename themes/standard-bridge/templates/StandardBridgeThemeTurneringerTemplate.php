<?php
declare(strict_types=1);

/**
 * Skabelon til en underside om et spillehold: turneringer og resultater
 * øverst og fakta om holdet nederst.
 */
final class StandardBridgeThemeTurneringerTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standardbridge-turneringer';
    }

    public static function name(): string
    {
        return 'Turneringer';
    }

    public static function description(): string
    {
        return 'Underside til et spillehold: turneringer, resultater og fakta om holdet.';
    }

    public static function sortOrder(): int
    {
        return 20;
    }

    public static function blocks(): array
    {
        return [
            static::block('standardbridge-turneringer'),
            static::block('standardbridge-omspilleholdet'),
        ];
    }
}
