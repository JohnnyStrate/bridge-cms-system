<?php
declare(strict_types=1);

// Klassen SKAL hedde det samme som filen, ellers bliver skabelonen ikke fundet.
final class StandardBridgeThemeForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standardbridge-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Hero, velkommen, ny i bridge og mesterpoint.';
    }

    // Lavest først. Den første skabelon er den, øjet under "Tema" viser.
    public static function sortOrder(): int
    {
        return 10;
    }

    // Blokkene på siden, oppefra og ned. Navbar og footer kommer automatisk
    // med, fordi de står i temaets globals() — de skal IKKE stå her.
    // En blok uden indstillinger får sine egne standardværdier.
    public static function blocks(): array
    {
        return [
            static::block('standardbridge-hero'),
            static::block('standardbridge-welcome'),
            static::block('standardbridge-nyibridge'),
            static::block('standardbridge-klubstillinger'),
        ];
    }
}
