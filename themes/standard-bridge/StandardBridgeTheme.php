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
            'standardbridge-navbar' => StandardBridgeNavbarBlock::class,
        ];
    }

    public static function globals(): array
    {
        return [
            'header' => 'standardbridge-navbar',
        ];
    }
}