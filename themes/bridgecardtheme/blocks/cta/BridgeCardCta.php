<?php
declare(strict_types=1);

/**
 * Den blå sektion: ét baggrundsbillede, der ses gennem to store kulører,
 * og nederst til venstre overlinje, turkis overskrift, tekst og en rød knap.
 *
 * Billedet og formen vælges i editoren og skifter live:
 *   - Billedet: klik på det, eller vælg under Indhold.
 *   - Formen: "Billedets form" under Udseende (Kløver, Ruder, Hjerter, Spar).
 *
 * Standardbilledet er BridgeCardKit::PHOTO. Skift det dér, når det
 * endelige billede er klar.
 */
final class BridgeCardCtaBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-cta';
    }

    public static function label(): string
    {
        return 'Blå sektion med billede — Bridge Card';
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
                'default' => '“Spil bridge i Charlottelund”',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => '”It is a long established fact that a reader will be distracted by the readable content of”',
                'max'     => 400,
            ],
            ...BridgeCardKit::buttonFields('Se turneringer'),
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede (vises i formerne)',
                'default' => BridgeCardKit::PHOTO,
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        $c = BridgeCardKit::COLORS;

        return [
            'shape' => BridgeCardKit::suitField('Billedets form'),
            'grayscale' => [
                'type'    => 'number',
                'label'   => 'Sort/hvid',
                'default' => 100,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Form',
            ],
            ...BridgeCardKit::backgroundFields($c['blue_bg'], $c['blue_bg']),
            'suit' => BridgeCardKit::suitField('Kulør ved overlinjen', 'Kløver', 'Tekst'),
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje og kulør',
                'default' => $c['teal_text'],
                'group'   => 'Tekst',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => $c['teal_text'],
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 48,
                'min'     => 22,
                'max'     => 96,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => $c['white'],
                'group'   => 'Tekst',
            ],
            'button_color' => BridgeCardKit::buttonColorField(),
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
            'buttonHref'  => BridgeCardKit::buttonHref($settings, $context),
            'bgImage'     => $context->asset((string) ($settings['bg_image'] ?? '')),
            'shape'       => BridgeCardKit::pick($styles['shape'] ?? '', BridgeCardKit::SUITS),
            'suit'        => BridgeCardKit::pick($styles['suit'] ?? '', BridgeCardKit::SUITS),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
