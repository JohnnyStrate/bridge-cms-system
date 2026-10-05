<?php
declare(strict_types=1);

/**
 * "Velkommen til din klub": overskrift, kort tekst og en række kort.
 *
 * Hvert kort har et billede, en titel og en tekst. Når musen er over
 * billedet, bliver det mørkt, og en hvid knap kommer frem.
 */
final class StandardBridgeWelcome extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-welcome';
    }

    public static function label(): string
    {
        return 'Velkommen — standardbridge';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        // Standardbilledet på kortene. Læg et billede i temaets assets-mappe
        // og skriv stien her, fx 'themes/standard-bridge/assets/kort.jpg'.
        // Tom = en mørk flade, indtil man vælger et billede i editoren.
        $image = '';

        $card = static fn (string $title, string $text, string $button): array => [
            'image'        => $image,
            'title'        => $title,
            'text'         => $text,
            'button_label' => $button,
            'page'         => 0,
            'url'          => '#',
        ];

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Velkommen til din klub',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst under overskriften',
                'default' => 'Vælg det spillehold eller den aktivitet, der passer dig. Hvert spillehold har sin egen side med turneringer og resultater.',
                'max'     => 300,
            ],
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort (billede, titel, tekst, knaptekst, side, adresse)',
                'max_rows' => 6,
                'fields'   => [
                    'image'        => ['type' => 'image', 'label' => 'Billede', 'default' => ''],
                    'title'        => ['type' => 'text', 'label' => 'Titel', 'default' => '', 'max' => 60],
                    'text'         => ['type' => 'textarea', 'label' => 'Tekst', 'default' => '', 'max' => 260],
                    'button_label' => ['type' => 'text', 'label' => 'Knaptekst', 'default' => '', 'max' => 30],
                    'page'         => ['type' => 'page', 'label' => 'Side', 'default' => 0],
                    'url'          => ['type' => 'url', 'label' => 'Ekstern adresse', 'default' => ''],
                ],
                'default' => [
                    $card(
                        'Mandag eftermiddag',
                        'Mandag 18.45 – 22.15 · Turneringsbridge og åbent hus. Nye spillere og gæster er velkomne.',
                        'Se mandag'
                    ),
                    $card(
                        'Mandag aften / åbent hus',
                        'Mandag 14.00 – 17.30 · Turneringsbridge · Fast eftermiddagshold med turneringsbridge i Ordrup Sognegård.',
                        'Se mandag'
                    ),
                    $card(
                        'Sommer & sølvturneringer',
                        'Mandag 14.00 – 17.30 · Turneringsbridge · Fast eftermiddagshold med turneringsbridge i Ordrup Sognegård.',
                        'Se turneringer'
                    ),
                ],
            ],
        ];
    }

    // UDSEENDE — bliver til CSS-variabler i block.css:
    // bg_color → --bg-color, title_color → --title-color osv.
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
            'card_title_color' => [
                'type'    => 'color',
                'label'   => 'Titel',
                'default' => '#524848',
                'group'   => 'Kort',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#1f1f1f',
                'group'   => 'Kort',
            ],
            'overlay' => [
                'type'    => 'number',
                'label'   => 'Mørkt lag ved hover',
                'default' => 80,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Kort',
            ],
            ...static::boxStyleFields('card', 'Kort', ['radius']),
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

            $cards[] = [
                'index'       => $index,
                'image'       => $context->asset((string) ($card['image'] ?? '')),
                'title'       => (string) ($card['title'] ?? ''),
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