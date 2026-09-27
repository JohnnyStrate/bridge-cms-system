<?php
declare(strict_types=1);

/**
 * Blå sektion med to billeder (øverst til venstre og til højre), overlinje,
 * turkis overskrift, tekst og en rød knap nederst til venstre.
 */
final class BridgeCardCtaBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-cta';
    }

    public static function label(): string
    {
        return 'Blå sektion med billeder — Bridge Card';
    }

    public static function getSchema(): array
    {
        return [
            'eyebrow' => [
                'type'    => 'text',
                'label'   => 'Lille overlinje',
                'default' => 'Vores spillehold',
                'max'     => 40,
            ],
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Spil bridge i Charlottelund',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'It is a long established fact that a reader will be distracted by the readable content of',
                'max'     => 400,
            ],
            ...BridgeCardKit::buttonFields('Se turneringer'),
            'image_left' => [
                'type'    => 'image',
                'label'   => 'Billede øverst til venstre',
                'default' => '',
            ],
            'image_right' => [
                'type'    => 'image',
                'label'   => 'Billede til højre',
                'default' => '',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            ...BridgeCardKit::backgroundFields('#1f4497', '#1f4497'),
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje og ikon',
                'default' => '#88e1d1',
                'group'   => 'Tekst',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#74d3c1',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 36,
                'min'     => 22,
                'max'     => 80,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Tekst',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#e8483f',
                'group'   => 'Knap',
            ],
            ...static::boxStyleFields('cta', 'Størrelse', ['height']),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        return static::renderTemplate([
            'eyebrow'     => trim((string) ($settings['eyebrow'] ?? '')),
            'title'       => (string) ($settings['title'] ?? ''),
            'text'        => (string) ($settings['text'] ?? ''),
            'buttonLabel' => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'  => BridgeCardKit::href(
                (int) ($settings['button_page'] ?? 0),
                (string) ($settings['button_url'] ?? ''),
                $context
            ),
            'imageLeft'   => $context->asset((string) ($settings['image_left'] ?? '')),
            'imageRight'  => $context->asset((string) ($settings['image_right'] ?? '')),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
