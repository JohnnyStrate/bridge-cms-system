<?php
declare(strict_types=1);

/**
 * Tema 1 — lyst og legende: cremefarvet navbar med skrå kant, piller med
 * hover-animation og en footer i samme stil.
 *
 * Nye blokke fra Figma-designet skrives ind i blocks() med 'tema1-' foran
 * typen, fx 'tema1-hero' => Tema1HeroBlock::class.
 */
final class Tema1Theme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Tema 1';
    }

    public static function description(): string
    {
        return 'Lys, cremefarvet stil med skrå kanter og animerede menupunkter.';
    }

    public static function sortOrder(): int
    {
        return 20;
    }

    public static function blocks(): array
    {
        return [
            'tema1-navbar' => Tema1NavbarBlock::class,
            'tema1-footer' => Tema1FooterBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'tema1-navbar',
            'footer' => 'tema1-footer',
        ];
    }
}
