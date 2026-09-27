<?php
declare(strict_types=1);

/**
 * Hero til Bridge Card: foto, en stor kulør bag den turkise overskrift,
 * kort tekst, en hvid knap og en linje nederst med telefon og adresse.
 *
 * Kuløren bag titlen vælges under Udseende og skifter live i editoren.
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
                'default' => BridgeCardKit::PHOTO,
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        $c = BridgeCardKit::COLORS;

        return [
            'suit' => BridgeCardKit::suitField('Form bag overskriften'),
            'suit_color' => [
                'type'    => 'color',
                'label'   => 'Formens farve',
                'default' => $c['dark'],
                'group'   => 'Form',
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
                'default' => 88,
                'min'     => 32,
                'max'     => 160,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => $c['white'],
                'group'   => 'Tekst',
            ],
            'hero_button' => [
                'type'    => 'color',
                'label'   => 'Knap: baggrund',
                'default' => $c['white'],
                'group'   => 'Knap',
            ],
            'hero_button_text' => [
                'type'    => 'color',
                'label'   => 'Knap: tekst',
                'default' => '#111111',
                'group'   => 'Knap',
            ],
            'overlay' => [
                'type'    => 'number',
                'label'   => 'Mørkt lag over billedet',
                'default' => 25,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'grayscale' => [
                'type'    => 'number',
                'label'   => 'Sort/hvid',
                'default' => 0,
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
            'buttonHref'  => BridgeCardKit::buttonHref($settings, $context),
            'phone'       => $phone,
            'phoneHref'   => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
            'address'     => (string) ($settings['address'] ?? ''),
            'bgImage'     => $context->asset((string) ($settings['bg_image'] ?? '')),
            'suit'        => BridgeCardKit::pick($styles['suit'] ?? '', BridgeCardKit::SUITS),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
