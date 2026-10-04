<?php
declare(strict_types=1);

/**
 * "Ny i bridge": et bredt billedbånd med overskriften på, og under det en
 * række korte oplysninger (fx spilstart, undervisning og pris).
 */
final class StandardBridgeNyIBridge extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-nyibridge';
    }

    public static function label(): string
    {
        return 'Ny i bridge';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Ny i bridge',
            ],
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede i båndet',
                'default' => 'themes/standard-bridge/assets/banner.png',
            ],
            'items' => [
                'type'     => 'repeater',
                'label'    => 'Oplysninger (lille overskrift, tekst)',
                'max_rows' => 6,
                'fields'   => [
                    'label' => ['type' => 'text', 'label' => 'Lille overskrift', 'default' => '', 'max' => 40],
                    'text'  => ['type' => 'textarea', 'label' => 'Tekst', 'default' => '', 'max' => 200],
                ],
                'default' => [
                    ['label' => 'Spilstart', 'text' => 'Spilstart kl. 18.45'],
                    ['label' => 'Undervisning', 'text' => 'Undervisning fra kl. 18.00'],
                    ['label' => 'Pris ved åbent hus', 'text' => '60 kr. pr. spiller pr. gang. Unge under 26 år spiller gratis.'],
                ],
            ],
        ];
    }

    // UDSEENDE — bliver til CSS-variabler i block.css.
    public static function getStyleSchema(): array
    {
        return [
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#f8f6f6',
                'group'   => 'Sektion',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#e6e6e6',
                'group'   => 'Bånd',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 44,
                'min'     => 20,
                'max'     => 96,
                'unit'    => 'px',
                'group'   => 'Bånd',
            ],
            // Auto = højden følger skærmens bredde (se block.css).
            ...static::boxStyleFields('banner', 'Bånd', ['height']),
            'label_color' => [
                'type'    => 'color',
                'label'   => 'Lille overskrift',
                'default' => '#000000',
                'group'   => 'Oplysninger',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#868686',
                'group'   => 'Oplysninger',
            ],
            'line_color' => [
                'type'    => 'color',
                'label'   => 'Streg',
                'default' => '#cbc8c8',
                'group'   => 'Oplysninger',
            ],
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        $items = [];

        foreach (array_values((array) $settings['items']) as $index => $item) {
            if (is_array($item)) {
                $items[] = [
                    'index' => $index,
                    'label' => (string) ($item['label'] ?? ''),
                    'text'  => (string) ($item['text'] ?? ''),
                ];
            }
        }

        return static::renderTemplate([
            'title'   => (string) $settings['title'],
            'image'   => $context->asset((string) $settings['image']),
            'items'   => $items,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
