<?php
declare(strict_types=1);

/**
 * Kort med ikoner: overlinje, overskrift, tekst og en række hvide kort med
 * ikon, titel, tekst og en rød knap.
 *
 * Bruges to gange på forsiden:
 *   - "Spil bridge i Charlottelund": hvid baggrund, tre kort, venstrestillet.
 *   - "Mesterpoint og point": turkis baggrund, fire kort, højrestillet.
 * Forskellen er kun indhold og Udseende.
 *
 * De to pynte-billeder (fx kløvere) lægges i hjørnerne bag indholdet.
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
        $card = static fn (string $icon, string $title): array => [
            'icon'         => $icon,
            'title'        => $title,
            'text'         => 'It is a long established fact that a reader will be distracted by the readable content of',
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
                        'options' => array_keys(BridgeCardKit::ICONS),
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
                        'max'     => 200,
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
                'default' => [
                    $card('Kalender', 'Spil bridge i Charlottelund'),
                    $card('Kalender', 'Spil bridge i Charlottelund'),
                    $card('Kalender', 'Spil bridge i Charlottelund'),
                ],
            ],
            'decor_left' => [
                'type'    => 'image',
                'label'   => 'Pynt: billede øverst til venstre',
                'default' => '',
            ],
            'decor_right' => [
                'type'    => 'image',
                'label'   => 'Pynt: billede øverst til højre',
                'default' => '',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'align' => [
                'type'    => 'select',
                'label'   => 'Overskrift og tekst',
                'default' => 'Venstre',
                'options' => ['Venstre', 'Højre'],
                'group'   => 'Layout',
            ],
            ...BridgeCardKit::backgroundFields('#ffffff', '#ffffff'),
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje',
                'default' => '#111111',
                'group'   => 'Tekst',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#1f4497',
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
                'default' => '#1f4497',
                'group'   => 'Tekst',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Kort: baggrund',
                'default' => '#ffffff',
                'group'   => 'Kort',
            ],
            'icon_color' => [
                'type'    => 'color',
                'label'   => 'Kort: ikon og titel',
                'default' => '#1f4497',
                'group'   => 'Kort',
            ],
            'card_text' => [
                'type'    => 'color',
                'label'   => 'Kort: tekst',
                'default' => '#7a7a7a',
                'group'   => 'Kort',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#e8483f',
                'group'   => 'Kort',
            ],
            ...static::boxStyleFields('card', 'Kort', ['radius'], ['radius' => 4]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $cards = [];

        foreach (array_values((array) ($settings['cards'] ?? [])) as $index => $card) {
            if (!is_array($card)) {
                continue;
            }

            $cards[] = [
                'index'       => $index,
                // Ikonet slås op i temaets egen liste.
                'icon'        => BridgeCardKit::ICONS[(string) ($card['icon'] ?? '')] ?? '',
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
            'decorLeft'  => $context->asset((string) ($settings['decor_left'] ?? '')),
            'decorRight' => $context->asset((string) ($settings['decor_right'] ?? '')),
            'cssVars'    => static::cssVariables($styles),
            'context'    => $context,
        ]);
    }
}
