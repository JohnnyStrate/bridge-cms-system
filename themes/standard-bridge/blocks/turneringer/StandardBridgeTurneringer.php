<?php
declare(strict_types=1);

/**
 * "Turneringer og resultater": overskrift og en række brede billedkort.
 *
 * Kortene virker som på forsiden: når musen er over billedet, bliver det
 * mørkt, og en hvid knap kommer frem.
 */
final class StandardBridgeTurneringer extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-turneringer';
    }

    public static function label(): string
    {
        return 'Turneringer og resultater';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        $image = 'themes/standard-bridge/assets/bgcards.png';

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Turneringer og resultater',
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
                    [
                        'image'        => $image,
                        'title'        => 'Turneringsoversigt',
                        'text'         => 'Se spilleholdets turneringer og spilleaftener i det officielle resultatsystem.',
                        'button_label' => 'Se turneringsoversigt',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'image'        => $image,
                        'title'        => 'Aktuelle turneringer',
                        'text'         => 'Se spilleholdets aktuelle turnering direkte i DBf\'s resultatsystem.',
                        'button_label' => 'Se aktuel turnering',
                        'page'         => 0,
                        'url'          => '#',
                    ],
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
                'default' => 48,
                'min'     => 24,
                'max'     => 96,
                'unit'    => 'px',
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
            'cards'   => $cards,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
