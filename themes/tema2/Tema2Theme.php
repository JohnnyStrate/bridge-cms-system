<?php
declare(strict_types=1);

/**
 * Tema 2 — mørkeblåt og roligt: fotohero med blåt filter og bølget
 * underkant, navbar som en gradient-pille og logo i venstre side.
 *
 * Farverne går igen i alle blokke:
 *     #192E3C → #457BA2   gradient på navbar og knapper
 *     #174765             filteret over hero-billedet
 *
 * Temaets egne billeder ligger i themes/tema2/assets/.
 *
 * Footeren kommer i en senere levering. Indtil da har temaet kun en
 * navbar i globals(), og der vises ingen footer.
 */
final class Tema2Theme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Tema 2';
    }

    public static function description(): string
    {
        return 'Mørkeblå gradient-navbar, fotohero med blåt filter og bølget kant.';
    }

    public static function sortOrder(): int
    {
        return 30;
    }

    public static function blocks(): array
    {
        return [
            'tema2-navbar'    => Tema2NavbarBlock::class,
            'tema2-hero'      => Tema2HeroBlock::class,
            'tema2-textimage' => Tema2TextImageBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'tema2-navbar',
        ];
    }
}
