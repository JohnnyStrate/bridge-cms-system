<?php
declare(strict_types=1);

/**
 * Hero til Bridge Card: sort/hvidt foto, stor turkis overskrift, kort tekst,
 * en hvid knap og en linje nederst med telefon og adresse.
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
                'default' => 'Koldinge Club',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'It is a long established fact that a reader will be distracted by the readable content of',
                'max'     => 300,
            ],
            ...BridgeCardKit::buttonFields('Se turneringer'),
            'phone' => [
                'type'    => 'text',
                'label'   => 'Telefon',
                'default' => '+45 41 25 12 74',
            ],
            'address' => [
                'type'    => 'text',
                'label'   => 'Adresse',
                'default' => 'Niels Bohrs vej 3, 6000 Kolding',
            ],
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede',
                'default' => '../themes/bridgecardtheme/assets/bgcardtheme.png',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#74d3c1',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 72,
                'min'     => 32,
                'max'     => 140,
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
                'label'   => 'Knap: baggrund',
                'default' => '#ffffff',
                'group'   => 'Knap',
            ],
            'button_text' => [
                'type'    => 'color',
                'label'   => 'Knap: tekst',
                'default' => '#111111',
                'group'   => 'Knap',
            ],
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund uden billede',
                'default' => '#3a3a3a',
                'group'   => 'Billede',
            ],
            'overlay' => [
                'type'    => 'number',
                'label'   => 'Mørkt lag over billedet',
                'default' => 35,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'grayscale' => [
                'type'    => 'number',
                'label'   => 'Sort/hvid',
                'default' => 100,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            ...static::boxStyleFields('hero', 'Størrelse', ['height']),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $phone = (string) ($settings['phone'] ?? '');

        return static::renderTemplate([
            'title'       => (string) ($settings['title'] ?? ''),
            'text'        => (string) ($settings['text'] ?? ''),
            'buttonLabel' => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'  => BridgeCardKit::href(
                (int) ($settings['button_page'] ?? 0),
                (string) ($settings['button_url'] ?? ''),
                $context
            ),
            'phone'       => $phone,
            'phoneHref'   => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
            'address'     => (string) ($settings['address'] ?? ''),
            'bgImage'     => $context->asset((string) ($settings['bg_image'] ?? '')),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
