<?php
declare(strict_types=1);

/**
 * Forside til Standard Bridge 2.
 *
 * Navbaren er IKKE med — den er temaets globale blok og ligger på alle
 * sider i forvejen. Flere sektioner skrives ind her, efterhånden som de
 * bliver bygget.
 */
final class Standard2ForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standard2-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Forside til Standard Bridge 2 med mørk fotohero. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            static::block('standard2-hero'),
            static::block('standard2-welcome'),
            static::block('standard2-banner'),
        ];
    }
}
