<?php
declare(strict_types=1);

/**
 * Forside til Bridge Card-temaet.
 */
final class BridgeCardForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'bridgecardtheme-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Forside med hero. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            static::block('bridgecardtheme-hero'),
        ];
    }
}
