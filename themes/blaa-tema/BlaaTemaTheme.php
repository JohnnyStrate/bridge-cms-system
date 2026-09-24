<?php
declare(strict_types=1);

/**
 * Det blå tema — projektets første.
 *
 * Blokkene her var de første og har derfor korte typer ('hero', 'navbar').
 * Nye temaer sætter temaet foran: 'tema1-hero'.
 */
final class BlaaTemaTheme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Blåt tema';
    }

    public static function description(): string
    {
        return 'Klassisk blå navbar og footer, hero med infoboks og velkomstsektion.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            'navbar'   => NavbarBlock::class,
            'footer'   => FooterBlock::class,
            'hero'     => HeroBlock::class,
            'welcome'  => WelcomeBlock::class,
            'boxcards' => BoxCardsBlock::class,
            'quote'    => QuoteBlock::class,
            'textbox'  => TextBoxBlock::class,
            'ranklist' => RankListBlock::class,
            'table'    => TableBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'navbar',
            'footer' => 'footer',
        ];
    }
}
