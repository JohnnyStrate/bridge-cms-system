<?php
declare(strict_types=1);

/**
 * Bridge Card-tema: turkis og blå med spillekort, Crimson Text i
 * overskrifterne og røde knapper.
 *
 * Farverne:
 *     #88E1D1 → #70C3B4   turkis gradient (spilletider)
 *     #1F4497             blå (overskrifter og CTA-sektion)
 *     #E8483F             røde knapper og kanter
 *
 * Temaets billeder lægges i themes/bridgecardtheme/assets/.
 */
final class BridgeCardTheme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Bridge Card';
    }

    public static function description(): string
    {
        return 'Turkis og blå med spillekort, Crimson Text og røde knapper.';
    }

    public static function sortOrder(): int
    {
        return 40;
    }

    public static function blocks(): array
    {
        return [
            'bridgecardtheme-navbar' => BridgeCardNavbarBlock::class,
            'bridgecardtheme-hero'   => BridgeCardHeroBlock::class,
            'bridgecardtheme-times'  => BridgeCardTimesBlock::class,
            'bridgecardtheme-cards'  => BridgeCardCardsBlock::class,
            'bridgecardtheme-cta'    => BridgeCardCtaBlock::class,
            'bridgecardtheme-footer' => BridgeCardFooterBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'bridgecardtheme-navbar',
            'footer' => 'bridgecardtheme-footer',
        ];
    }
}
