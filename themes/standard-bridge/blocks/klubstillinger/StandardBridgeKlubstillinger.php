<?php
declare(strict_types=1);

/**
 * "Mesterpoint og klubstillinger": overskrift, kort tekst og en række kort
 * i to kolonner.
 *
 * Hvert kort har et billedbånd med titlen på, et lille ikon, en tekst og
 * en mørk knap, der får en pil, når musen er over den.
 */
final class StandardBridgeKlubstillinger extends AbstractBlock
{
    // Ikonerne, man kan vælge på et kort: navn => [farve, SVG-tegning].
    // Et nyt ikon er én linje mere her.
    private const ICONS = [
        'Stjerne' => ['#99746b', '<path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.2l-6.1 3.4 1.4-6.8L2.2 9.1l6.9-.8z" fill="currentColor"/>'],
        'Pil op'  => ['#e04141', '<path d="M12 21V5M6.5 9.5 12 4l5.5 5.5" fill="none" stroke="currentColor" stroke-width="2.6"/>'],
        'Ingen'   => ['', ''],
    ];

    public static function type(): string
    {
        return 'standardbridge-klubstillinger';
    }

    public static function label(): string
    {
        return 'Mesterpoint og klubstillinger';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        $cards = 'themes/standard-bridge/assets/bannercards.png';
        $hand  = 'themes/standard-bridge/assets/bgcards.png';

        $card = static fn (string $image, string $title, string $icon, string $text): array => [
            'image'        => $image,
            'title'        => $title,
            'icon'         => $icon,
            'text'         => $text,
            'button_label' => $title,
            'page'         => 0,
            'url'          => '#',
        ];

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Mesterpoint og klubstillinger',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst under overskriften',
                'default' => 'Vælg det spillehold eller den aktivitet, der passer dig. Hvert spillehold har sin egen side med turneringer og resultater.',
                'max'     => 300,
            ],
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort (billede, titel, ikon, tekst, knaptekst, side, adresse)',
                'max_rows' => 8,
                'fields'   => [
                    'image' => ['type' => 'image', 'label' => 'Billede', 'default' => ''],
                    'title' => ['type' => 'text', 'label' => 'Titel', 'default' => '', 'max' => 40],
                    'icon'  => [
                        'type'    => 'select',
                        'label'   => 'Ikon',
                        'default' => 'Stjerne',
                        'options' => array_keys(self::ICONS),
                    ],
                    'text'         => ['type' => 'textarea', 'label' => 'Tekst', 'default' => '', 'max' => 260],
                    'button_label' => ['type' => 'text', 'label' => 'Knaptekst', 'default' => '', 'max' => 30],
                    'page'         => ['type' => 'page', 'label' => 'Side', 'default' => 0],
                    'url'          => ['type' => 'url', 'label' => 'Ekstern adresse', 'default' => ''],
                ],
                'default' => [
                    $card($cards, 'Mesterpoint', 'Stjerne', 'Se Gentofte Bridgeklubs medlemmer fordelt efter mesterpointtitel samt deres bronze-, sølv-, guld- og samlede mesterpoint.'),
                    $card($hand, 'Rangliste', 'Pil op', 'Se Gentofte Bridgeklubs officielle rangliste hos Danmarks Bridgeforbund.'),
                    $card($cards, 'Bronzestilling', 'Stjerne', 'Se Gentofte Bridgeklubs aktuelle bronzestilling.'),
                    $card($hand, 'Handicap', 'Pil op', 'Se medlemmernes aktuelle handicap og klubrangering.'),
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
                'default' => '#454545',
                'group'   => 'Sektion',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 56,
                'min'     => 24,
                'max'     => 96,
                'unit'    => 'px',
                'group'   => 'Sektion',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst under overskriften',
                'default' => '#a19999',
                'group'   => 'Sektion',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#f8f8f8',
                'group'   => 'Kort',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#0f0f0f',
                'group'   => 'Kort',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#3d3a19',
                'group'   => 'Kort',
            ],
            'overlay' => [
                'type'    => 'number',
                'label'   => 'Mørkt lag over billedet',
                'default' => 30,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Kort',
            ],
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        $cards = [];

        foreach (array_values((array) $settings['cards']) as $index => $card) {
            if (!is_array($card)) {
                continue;
            }

            $pageId = (int) ($card['page'] ?? 0);
            $url    = (string) ($card['url'] ?? '');

            // Ikonet slås op i vores egen liste — aldrig brugerens tekst.
            [$iconColor, $iconSvg] = self::ICONS[(string) ($card['icon'] ?? '')] ?? ['', ''];

            $cards[] = [
                'index'       => $index,
                'image'       => $context->asset((string) ($card['image'] ?? '')),
                'title'       => (string) ($card['title'] ?? ''),
                'iconColor'   => $iconColor,
                'iconSvg'     => $iconSvg,
                'text'        => (string) ($card['text'] ?? ''),
                'buttonLabel' => trim((string) ($card['button_label'] ?? '')),
                // En valgt side vinder over en skrevet adresse.
                'href'        => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        return static::renderTemplate([
            'title'   => (string) $settings['title'],
            'text'    => (string) $settings['text'],
            'cards'   => $cards,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
