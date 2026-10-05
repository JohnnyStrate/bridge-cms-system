<?php
declare(strict_types=1);

/**
 * Standard Bridge 2 — mørkt, stramt og roligt.
 *
 * Et af de to standardtemaer. Version 1 er det, der er aktivt fra start;
 * dette er alternativet med samme formål, men et andet udtryk: mørk
 * fotohero, tynde streger, skarpe hjørner og Jost i lette vægte.
 *
 * Farverne går igen i blokkene:
 *     #474748 @ 39 %   overlay på fotoet
 *     #D8D8D8          store overskrifter og aktiv menu
 *     #8F8F8F          menupunkter
 *     #F6ECEC          knapper (fyldt / streg)
 *     #484848          tekst på lys knap
 *     #5B5959          sektionsoverskrift (streg + boks)
 *     #EFEFEF          sidens baggrund under hero'en
 *
 * Temaets egne billeder ligger i themes/standard2/assets/.
 * Fælles CSS ligger i theme.css, fælles PHP (sektionsoverskrift og den
 * rolige indgang) i Standard2Kit.php.
 */
final class Standard2Theme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Standard Bridge 2';
    }

    public static function description(): string
    {
        return 'Mørk fotohero, tynde streger og skarpe hjørner. Stramt og roligt.';
    }

    public static function sortOrder(): int
    {
        return 12;
    }

    public static function blocks(): array
    {
        return [
            'standard2-navbar'  => Standard2NavbarBlock::class,
            'standard2-hero'    => Standard2HeroBlock::class,
            'standard2-welcome' => Standard2WelcomeBlock::class,
            'standard2-banner'  => Standard2BannerBlock::class,
            'standard2-cards'   => Standard2CardsBlock::class,
            'standard2-facts'   => Standard2FactsBlock::class,
            'standard2-grid'    => Standard2GridBlock::class,
            'standard2-footer'  => Standard2FooterBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'standard2-navbar',
            'footer' => 'standard2-footer',
        ];
    }
}
