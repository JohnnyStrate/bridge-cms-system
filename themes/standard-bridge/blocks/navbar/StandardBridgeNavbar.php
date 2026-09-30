<?php
declare(strict_types=1);

final class StandardBridgeTheme extends AbstractTheme
{
    public static function name(): string
    {
        return 'Standard Bridge';
    }

    public static function description(): string
    {
        return 'Beskrivelse af temaet.';
    }

    public static function sortOrder(): int
    {
        return 50;
    }

    public static function isReady(): bool
    {
        return false;
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