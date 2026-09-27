<?php
declare(strict_types=1);

/**
 * Hero til Bridge Card-temaet: baggrundsbillede, overskrift og en kort tekst.
 */
final class BridgeCardHeroBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-hero';
    }

    public static function label(): string
    {
        return 'Hero — Bridge Card';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Din Bridgeklub',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'Skriv et par linjer om klubben her.',
                'max'     => 400,
            ],
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede',
                'default' => 'assets/demo/hero-placeholder.jpg',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve',
                'default' => '#ffffff',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 64,
                'min'     => 24,
                'max'     => 140,
                'unit'    => 'px',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],
            ...static::boxStyleFields('hero', 'Hero', ['height']),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        return static::renderTemplate([
            'title'   => (string) ($settings['title'] ?? ''),
            'text'    => (string) ($settings['text'] ?? ''),
            'bgImage' => $context->asset((string) ($settings['bg_image'] ?? '')),
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
