<?php
declare(strict_types=1);

/**
 * Kort med ikoner: overlinje med kulør, overskrift, tekst og en række
 * hvide kort med ikon, titel, tekst og en rød knap. Kulører er strøet ud
 * som pynt bag indholdet.
 *
 * Bruges to gange på forsiden:
 *   - "Spil bridge i Charlottelund": hvid, tre kort, venstrestillet,
 *     turkis pynt til højre.
 *   - "Mesterpoint og point": turkis, fire kort, højrestillet, blå pynt
 *     til venstre.
 * Forskellen er kun indhold og Udseende.
 *
 * Kortenes ikoner, overlinjens kulør og pyntens former skifter live i
 * editoren (data-live, se BridgeCardKit::live()).
 */
final class BridgeCardCardsBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-cards';
    }

    public static function label(): string
    {
        return 'Kort med ikoner — Bridge Card';
    }

    public static function getSchema(): array
    {
        $text = 'It is a long established fact that a reader will be distracted by the readable content of It is a long established fact that a reader will be distracted by the readable content of';
        $card = static fn (): array => [
            'icon'         => 'Kalender',
            'title'        => 'Spil bridge i charlottelund',
            'text'         => $text,
            'button_label' => 'Se turneringer',
            'page'         => 0,
            'url'          => '#',
        ];

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
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort (ikon, titel, tekst, knaptekst, side, adresse)',
                'max_rows' => 8,
                'fields'   => [
                    'icon' => [
                        'type'    => 'select',
                        'label'   => 'Ikon',
                        'default' => 'Kalender',
                        'options' => BridgeCardKit::ICONS,
                    ],
                    'title' => [
                        'type'    => 'text',
                        'label'   => 'Titel',
                        'default' => '',
                        'max'     => 60,
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst',
                        'default' => '',
                        'max'     => 260,
                    ],
                    'button_label' => [
                        'type'    => 'text',
                        'label'   => 'Knaptekst',
                        'default' => '',
                        'max'     => 30,
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'    => 'url',
                        'label'   => 'Ekstern adresse',
                        'default' => '',
                    ],
                ],
                'default' => [$card(), $card(), $card()],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        $c = BridgeCardKit::COLORS;

        return [
            'align' => [
                'type'    => 'select',
                'label'   => 'Overskrift og tekst',
                'default' => 'Venstre',
                'options' => ['Venstre', 'Højre'],
                'group'   => 'Layout',
            ],
            ...BridgeCardKit::backgroundFields($c['white'], $c['white']),
            'suit' => BridgeCardKit::suitField('Kulør ved overlinjen'),
            'decor_suit' => BridgeCardKit::suitField('Pynt: store former', 'Kløver'),
            'decor_suit_small' => BridgeCardKit::suitField('Pynt: små former', 'Spar'),
            'decor_color' => [
                'type'    => 'color',
                'label'   => 'Pynt: farve',
                'default' => $c['teal_text'],
                'group'   => 'Form',
            ],
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje',
                'default' => '#111111',
                'group'   => 'Tekst',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => $c['blue'],
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
                'default' => '#444444',
                'group'   => 'Tekst',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Kort: baggrund',
                'default' => $c['white'],
                'group'   => 'Kort',
            ],
            'icon_color' => [
                'type'    => 'color',
                'label'   => 'Kort: ikon og titel',
                'default' => $c['blue'],
                'group'   => 'Kort',
            ],
            'card_text' => [
                'type'    => 'color',
                'label'   => 'Kort: tekst',
                'default' => $c['grey'],
                'group'   => 'Kort',
            ],
            'button_color' => BridgeCardKit::buttonColorField(),
            ...static::boxStyleFields('card', 'Kort', ['radius'], ['radius' => 8]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $cards = [];

        foreach (BridgeCardKit::rows($settings['cards'] ?? []) as $card) {
            $cards[] = [
                'index'       => (int) $card['index'],
                'icon'        => BridgeCardKit::pick($card['icon'] ?? '', BridgeCardKit::ICONS),
                'title'       => (string) ($card['title'] ?? ''),
                'text'        => (string) ($card['text'] ?? ''),
                'buttonLabel' => trim((string) ($card['button_label'] ?? '')),
                'href'        => BridgeCardKit::href((int) ($card['page'] ?? 0), (string) ($card['url'] ?? ''), $context),
            ];
        }

        return static::renderTemplate([
            'eyebrow'    => trim((string) ($settings['eyebrow'] ?? '')),
            'title'      => (string) ($settings['title'] ?? ''),
            'text'       => (string) ($settings['text'] ?? ''),
            'cards'      => $cards,
            'alignRight' => ($styles['align'] ?? 'Venstre') === 'Højre',
            'suit'       => BridgeCardKit::pick($styles['suit'] ?? '', BridgeCardKit::SUITS),
            'decorBig'   => BridgeCardKit::pick($styles['decor_suit'] ?? '', BridgeCardKit::SUITS),
            'decorSmall' => BridgeCardKit::pick($styles['decor_suit_small'] ?? '', BridgeCardKit::SUITS),
            'cssVars'    => static::cssVariables($styles),
            'context'    => $context,
        ]);
    }
}
