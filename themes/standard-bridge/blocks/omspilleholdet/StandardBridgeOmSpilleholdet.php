<?php
declare(strict_types=1);

/**
 * "Om spilleholdet": overskrift og en grå boks med korte fakta i to
 * kolonner (fx hovedklub, klubnummer, aktivitet og spillested).
 * Nederst i boksen kan der stå et lille link.
 */
final class StandardBridgeOmSpilleholdet extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-omspilleholdet';
    }

    public static function label(): string
    {
        return 'Om spilleholdet';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Om spilleholdet',
            ],
            'items' => [
                'type'     => 'repeater',
                'label'    => 'Fakta (lille overskrift, tekst)',
                'max_rows' => 8,
                'fields'   => [
                    'label' => ['type' => 'text', 'label' => 'Lille overskrift', 'default' => '', 'max' => 40],
                    'value' => ['type' => 'text', 'label' => 'Tekst', 'default' => '', 'max' => 80],
                ],
                'default' => [
                    ['label' => 'Hovedklub', 'value' => 'Gentofte Bridgeklub'],
                    ['label' => 'Klubnummer', 'value' => '2132 / 4'],
                    ['label' => 'Aktivitet', 'value' => 'Turneringsbridge'],
                    ['label' => 'Spillested', 'value' => 'Ordrup Sognegård'],
                ],
            ],
            'link_label' => [
                'type'        => 'text',
                'label'       => 'Link nederst: tekst',
                'placeholder' => 'Tom = intet link',
                'default'     => 'Se klubben',
                'max'         => 40,
            ],
            'link_page' => ['type' => 'page', 'label' => 'Link nederst: side', 'default' => 0],
            'link_url'  => ['type' => 'url', 'label' => 'Link nederst: ekstern adresse', 'default' => ''],
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
            'box_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#f0eeee',
                'group'   => 'Boks',
            ],
            'label_color' => [
                'type'    => 'color',
                'label'   => 'Lille overskrift',
                'default' => '#a5a5a5',
                'group'   => 'Boks',
            ],
            'value_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#484848',
                'group'   => 'Boks',
            ],
            ...static::boxStyleFields('box', 'Boks', ['radius']),
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        $items = [];

        foreach (array_values((array) $settings['items']) as $index => $item) {
            if (is_array($item)) {
                $items[] = [
                    'index' => $index,
                    'label' => (string) ($item['label'] ?? ''),
                    'value' => (string) ($item['value'] ?? ''),
                ];
            }
        }

        $pageId = (int) $settings['link_page'];
        $url    = (string) $settings['link_url'];

        return static::renderTemplate([
            'title'     => (string) $settings['title'],
            'items'     => $items,
            'linkLabel' => trim((string) $settings['link_label']),
            // En valgt side vinder over en skrevet adresse.
            'linkHref'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            'cssVars'   => static::cssVariables($styles),
            'context'   => $context,
        ]);
    }
}
