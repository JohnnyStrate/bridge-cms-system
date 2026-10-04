<?php
declare(strict_types=1);

final class StandardBridgeTheme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Standard-bridge-theme';
    }

    public static function description(): string
    {
        return 'beskrivelse af Khalids grimme description';
    }

    public static function sortOrder(): int
    {
        return 20;
    }

    public static function isReady(): bool
    {
        return true;
    }

    public static function blocks(): array
    {
        return [
            'standardbridge-navbar'         => StandardBridgeNavbarBlock::class,
            'standardbridge-footer'         => StandardBridgeFooter::class,
            'standardbridge-hero'           => StandardBridgeHero::class,
            'standardbridge-welcome'        => StandardBridgeWelcome::class,
            'standardbridge-nyibridge'      => StandardBridgeNyIBridge::class,
            'standardbridge-klubstillinger' => StandardBridgeKlubstillinger::class,
            'standardbridge-turneringer'    => StandardBridgeTurneringer::class,
            'standardbridge-omspilleholdet' => StandardBridgeOmSpilleholdet::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'standardbridge-navbar',
            'footer' => 'standardbridge-footer',
        ];
    }
}
