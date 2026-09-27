<?php
declare(strict_types=1);

/**
 * Spilletider: turkis gradient, kløver-ikon, overskrift, tekst, en række
 * hvide info-bokse med rød kant og et billede til højre.
 *
 * Gradienten går fra #88E1D1 i toppen til #70C3B4 i bunden. Sættes begge
 * farver ens, bliver det en ensfarvet baggrund.
 */
final class BridgeCardTimesBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-times';
    }

    public static function label(): string
    {
        return 'Spilletider — Bridge Card';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Spilletider i Charlottelund',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'It is a long established fact that a reader will be distracted by the readable content of',
                'max'     => 400,
            ],
            'items' => [
                'type'     => 'repeater',
                'label'    => 'Info-bokse (lille overskrift, tekst)',
                'max_rows' => 6,
                'fields'   => [
                    'label' => ['type' => 'text', 'label' => 'Lille overskrift', 'default' => '', 'max' => 40],
                    'value' => ['type' => 'text', 'label' => 'Tekst', 'default' => '', 'max' => 120],
                ],
                'default' => [
                    ['label' => 'Vores spillehold', 'value' => 'Spilstart: 18:00'],
                    ['label' => 'Undervisning', 'value' => 'Undervisning kl 13:40'],
                    ['label' => 'Priser', 'value' => '60 kr pr spiller pr gng unge under 16 år spiller gratis'],
                ],
            ],
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede til højre',
                'default' => 'themes/bridgecardtheme/assets/cardspicture.png',
            ],
            'image_alt' => [
                'type'    => 'text',
                'label'   => 'Billede: beskrivelse (alt-tekst)',
                'default' => '',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            ...BridgeCardKit::backgroundFields('#88e1d1', '#70c3b4'),
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift og ikon',
                'default' => '#ffffff',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 40,
                'min'     => 24,
                'max'     => 96,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#1f4497',
                'group'   => 'Tekst',
            ],
            'box_color' => [
                'type'    => 'color',
                'label'   => 'Bokse: baggrund',
                'default' => '#ffffff',
                'group'   => 'Bokse',
            ],
            'box_text' => [
                'type'    => 'color',
                'label'   => 'Bokse: tekst',
                'default' => '#1f4497',
                'group'   => 'Bokse',
            ],
            'accent_color' => [
                'type'    => 'color',
                'label'   => 'Bokse: kant',
                'default' => '#e8483f',
                'group'   => 'Bokse',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $items = [];

        foreach (array_values((array) ($settings['items'] ?? [])) as $index => $row) {
            if (is_array($row)) {
                $items[] = [
                    'index' => $index,
                    'label' => (string) ($row['label'] ?? ''),
                    'value' => (string) ($row['value'] ?? ''),
                ];
            }
        }

        return static::renderTemplate([
            'title'    => (string) ($settings['title'] ?? ''),
            'text'     => (string) ($settings['text'] ?? ''),
            'items'    => $items,
            'image'    => $context->asset((string) ($settings['image'] ?? '')),
            'imageAlt' => (string) ($settings['image_alt'] ?? ''),
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
        ]);
    }
}
