<?php
declare(strict_types=1);

/**
 * Tema 3 — rødt, blødt og "boblet": runde former, bløde skygger og en
 * svag rosa baggrund, der går over i hvid.
 *
 * Farverne går igen i alle blokke:
 *     #CC5656 → #CA6A6A   gradient på navbaren
 *     #3D3A3A → #C83030   gradient på store overskrifter
 *     #D55C5C             brødtekst i rødt
 *     #BCB6B6 / #E36D6D   knapper (hvile / hover)
 *     #EFB7B7 → #FFFFFF   sidens baggrund (se navbarens block.css)
 *
 * Temaets egne billeder ligger i themes/tema3/assets/. Hero'en låner
 * indtil videre tema 2's dummybillede.
 *
 * Navbar og footer er temaets globale blokke (se globals()).
 */
final class Tema3Theme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Tema 3';
    }

    public static function description(): string
    {
        return 'Rødt og blødt: boblet navbar, stort billede fra venstre og røde gradient-overskrifter.';
    }

    public static function sortOrder(): int
    {
        return 40;
    }

    public static function blocks(): array
    {
        return [
            'tema3-navbar'   => Tema3NavbarBlock::class,
            'tema3-hero'     => Tema3HeroBlock::class,
            'tema3-info'     => Tema3InfoBlock::class,
            'tema3-schedule' => Tema3ScheduleBlock::class,
            'tema3-slider'   => Tema3SliderBlock::class,
            'tema3-links'    => Tema3LinksBlock::class,
            'tema3-footer'   => Tema3FooterBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'tema3-navbar',
            'footer' => 'tema3-footer',
        ];
    }
}
