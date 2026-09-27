<?php
declare(strict_types=1);

/**
 * Forside til tema 3.
 *
 * Navbaren er IKKE med — den er temaets globale blok og ligger på alle
 * sider i forvejen.
 *
 * Flere sektioner skrives ind her, efterhånden som de bliver bygget.
 */
final class Tema3ForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'tema3-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Forside til tema 3 med stort billede og to knapper. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            static::block('tema3-hero'),
            static::block('tema3-info'),
            static::block('tema3-schedule'),
            static::block('tema3-slider'),
            static::block('tema3-links'),
        ];
    }
}
