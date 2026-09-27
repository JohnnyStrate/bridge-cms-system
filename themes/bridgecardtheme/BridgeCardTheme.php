<?php
declare(strict_types=1);

/**
 * Bridge Card-tema.
 *
 * Indtil videre kun en hero. Navbar og footer kommer senere og skrives
 * ind i blocks() og globals(), når de er bygget.
 */
final class BridgeCardTheme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Bridge Card';
    }

    public static function description(): string
    {
        return 'Nyt tema — under opbygning.';
    }

    public static function sortOrder(): int
    {
        return 40;
    }

    public static function blocks(): array
    {
        return [
            'bridgecardtheme-hero' => BridgeCardHeroBlock::class,
        ];
    }

    public static function globals(): array
    {
        // Ingen navbar/footer endnu.
        return [];
    }
}
