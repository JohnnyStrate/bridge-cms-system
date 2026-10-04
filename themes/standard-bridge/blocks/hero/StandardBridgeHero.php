<?php
declare(strict_types=1);

final class StandardBridgeHero extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-hero';
    }

    public static function label(): string
    {
        return 'Hero — standardbridge';
    }

    public static function getSchema(): array
    {
        return [
            'title' => ['type' => 'text', 'label' => 'Overskrift', 'default' => 'Din Bridgeklub'],
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        return static::renderTemplate([
            'title'   => $settings['title'],
            'context' => $context,
        ]);
    }
}
