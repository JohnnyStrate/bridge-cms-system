<?php
declare(strict_types=1);

/**
 * Forside til tema 1.
 *
 * Navbar og footer er IKKE med. De er temaets globale blokke og ligger på
 * alle sider i forvejen.
 *
 * Indtil tema 1 har sine egne sektioner fra Figma, bruges en fælles
 * tekstblok som pladsholder. Når 'tema1-hero' er bygget og står i
 * Tema1Theme::blocks(), skrives den ind øverst her.
 */
final class Tema1ForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'tema1-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Forside til tema 1. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            // Felter, der ikke nævnes, får blokkens egen standardværdi.
            static::block('textarea', [
                'title' => 'Velkommen til klubben',
                'body'  => 'Skriv en kort introduktion til jeres klub her.',
            ]),
        ];
    }
}
