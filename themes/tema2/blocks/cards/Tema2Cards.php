<?php
declare(strict_types=1);

/**
 * Kort med ikoner til tema 2 ("Vores spillehold").
 *
 * Lys grå flade med overlinje og overskrift, og under dem en række hvide
 * kort. Hvert kort har et ikon i hjørnet, en titel, en kort tekst og en
 * lille knap i bunden.
 *
 * IKONERNE
 * Ikonerne er tegnet i kode (se ICONS nedenfor) og vælges fra en liste pr.
 * kort. Det holder dem i samme stil og farve som resten af temaet, de kan
 * aldrig mangle som fil, og farven kan ændres for alle på én gang under
 * Udseende. Et nyt ikon er én linje mere i ICONS.
 */
final class Tema2CardsBlock extends AbstractBlock
{
    /**
     * Ikonernes navn i editoren => SVG-indhold (viewBox 0 0 24 24).
     * 'currentColor' er ikonfarven, hvid er symbolet oven på den.
     */
    public const ICONS = [
        'Stjerne' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<path d="M12 5.6l1.9 3.9 4.3.6-3.1 3 .7 4.3L12 15.4l-3.8 2 .7-4.3-3.1-3 4.3-.6z" fill="#fff"/>',
        'Pil op' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<path d="M12 17.5V7M7.5 11.3 12 6.8l4.5 4.5" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
        'Medalje' => '<path d="M8.6 13.5 6.8 23l5.2-2.6 5.2 2.6-1.8-9.5z" fill="#7a1f2b"/>'
            . '<path d="M12 1.2l1.9 1.5 2.4-.3.9 2.2 2.2.9-.3 2.4 1.5 1.9-1.5 1.9.3 2.4-2.2.9-.9 2.2-2.4-.3-1.9 1.5-1.9-1.5-2.4.3-.9-2.2-2.2-.9.3-2.4L2.4 9.8l1.5-1.9-.3-2.4 2.2-.9.9-2.2 2.4.3z" fill="currentColor"/>'
            . '<circle cx="12" cy="9.8" r="4.3" fill="#fff" opacity=".35"/>',
        'Cirkel' => '<defs><linearGradient id="t2c-grad" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0" stop-color="#fde68a"/><stop offset="1" stop-color="currentColor"/>'
            . '</linearGradient></defs><circle cx="12" cy="12" r="11" fill="url(#t2c-grad)"/>',
        'Trofæ' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<path d="M8.5 6.5h7v3.2a3.5 3.5 0 0 1-7 0zM12 13.2v2.6M9.3 17.5h5.4M8.5 7.6H6.6a2 2 0 0 0 2.2 3M15.5 7.6h1.9a2 2 0 0 1-2.2 3" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'Kalender' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<rect x="6.8" y="7.8" width="10.4" height="9.4" rx="1.6" fill="none" stroke="#fff" stroke-width="1.5"/>'
            . '<path d="M6.8 11h10.4M9.6 6.3v2.6M14.4 6.3v2.6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>',
        'Hjerter' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<path d="M12 17.3s-5.2-3.2-5.2-6.6a2.8 2.8 0 0 1 5.2-1.5 2.8 2.8 0 0 1 5.2 1.5c0 3.4-5.2 6.6-5.2 6.6z" fill="#fff"/>',
        'Spar' => '<circle cx="12" cy="12" r="11" fill="currentColor"/>'
            . '<path d="M12 5.8s-5 3.6-5 6.9a2.6 2.6 0 0 0 4.3 2l-.8 2.9h3l-.8-2.9a2.6 2.6 0 0 0 4.3-2c0-3.3-5-6.9-5-6.9z" fill="#fff"/>',
        'Ingen' => '',
    ];

    public static function type(): string
    {
        return 'tema2-cards';
    }

    public static function label(): string
    {
        return 'Kort med ikoner — tema 2';
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
                'default' => 'Bridge i jeres by',
            ],
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort (ikon, titel, tekst, knaptekst, side, adresse)',
                'max_rows' => 8,
                'fields'   => [
                    'icon' => [
                        'type'    => 'select',
                        'label'   => 'Ikon',
                        'default' => 'Stjerne',
                        'options' => array_keys(self::ICONS),
                    ],
                    'title' => [
                        'type'    => 'text',
                        'label'   => 'Titel',
                        'default' => '',
                        'max'     => 40,
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst (maks. 160 tegn)',
                        'default' => '',
                        'max'     => 160,
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
                    [
                        'icon'         => 'Stjerne',
                        'title'        => 'Mesterpoint',
                        'text'         => 'Skriv kort, hvad man finder her — fx medlemmernes mesterpoint fordelt på titler.',
                        'button_label' => 'Se mesterpoint',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'icon'         => 'Pil op',
                        'title'        => 'Rangliste',
                        'text'         => 'Link fx til klubbens officielle rangliste hos jeres forbund.',
                        'button_label' => 'Se rangliste',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'icon'         => 'Medalje',
                        'title'        => 'Bronzestilling',
                        'text'         => 'Vis den aktuelle stilling i klubbens bronzeturnering.',
                        'button_label' => 'Se stilling',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'icon'         => 'Cirkel',
                        'title'        => 'Handicap',
                        'text'         => 'Vis medlemmernes aktuelle handicap og klubrangering.',
                        'button_label' => 'Se handicapliste',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#fafafa',
                'group'   => 'Farver',
            ],
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje',
                'default' => '#c0c0c0',
                'group'   => 'Farver',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#343d39',
                'group'   => 'Farver',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Kort',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst i kort',
                'default' => '#111111',
                'group'   => 'Farver',
            ],
            'icon_color' => [
                'type'    => 'color',
                'label'   => 'Ikoner',
                'default' => '#f0a21e',
                'group'   => 'Farver',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 44,
                'min'     => 24,
                'max'     => 96,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Tekst',
            ],
            'shadow_color' => [
                'type'    => 'color',
                'label'   => 'Kortenes kant/skygge',
                'default' => '#817474',
                'group'   => 'Kort',
            ],
            // Lille blur = en tynd streg om kortet. Stor blur = en blød skygge.
            'shadow_blur' => [
                'type'    => 'number',
                'label'   => 'Skyggens blødhed (lav = streg)',
                'default' => 13,
                'min'     => 0,
                'max'     => 40,
                'unit'    => 'px',
                'group'   => 'Kort',
            ],
            'card_height' => [
                'type'    => 'number',
                'label'   => 'Kortenes højde',
                'default' => 336,
                'min'     => 200,
                'max'     => 600,
                'unit'    => 'px',
                'group'   => 'Kort',
            ],
            'button_start' => [
                'type'    => 'color',
                'label'   => 'Knap: farve til venstre',
                'default' => '#192e3c',
                'group'   => 'Knap',
            ],
            'button_end' => [
                'type'    => 'color',
                'label'   => 'Knap: farve til højre',
                'default' => '#457ba2',
                'group'   => 'Knap',
            ],
            'button_opacity' => [
                'type'    => 'number',
                'label'   => 'Knap: dækkeevne',
                'default' => 72,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Knap',
            ],
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

            $pageId = (int) ($card['page'] ?? 0);
            $url    = (string) ($card['url'] ?? '');
            $icon   = (string) ($card['icon'] ?? '');

            $cards[] = [
                'index'       => $index,
                // Ikonet slås op i vores egen liste — aldrig brugerens tekst
                // direkte ind i siden.
                'icon'        => self::ICONS[$icon] ?? '',
                'title'       => (string) ($card['title'] ?? ''),
                'text'        => (string) ($card['text'] ?? ''),
                'buttonLabel' => trim((string) ($card['button_label'] ?? '')),
                'href'        => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        return static::renderTemplate([
            'eyebrow' => trim((string) ($settings['eyebrow'] ?? '')),
            'title'   => (string) ($settings['title'] ?? ''),
            'cards'   => $cards,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
