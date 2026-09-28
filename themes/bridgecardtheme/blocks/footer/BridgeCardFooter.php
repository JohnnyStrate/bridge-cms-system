<?php
declare(strict_types=1);

/**
 * Footer til Bridge Card: tre kolonner på mørk baggrund —
 * spilletider, sider (links) og information.
 */
final class BridgeCardFooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-footer';
    }

    public static function label(): string
    {
        return 'Footer — Bridge Card';
    }

    public static function getSchema(): array
    {
        $day  = static fn (string $d): array => ['day' => $d, 'time' => '20:00 - 21:00'];
        $link = ['label' => 'Link', 'page' => 0, 'url' => '#'];

        return [
            'times_title' => [
                'type'    => 'text',
                'label'   => 'Kolonne 1: overskrift',
                'default' => 'Spilletider',
            ],
            'times' => [
                'type'     => 'repeater',
                'label'    => 'Kolonne 1: dage og tider',
                'max_rows' => 7,
                'fields'   => [
                    'day'  => ['type' => 'text', 'label' => 'Dag', 'default' => '', 'max' => 30],
                    'time' => ['type' => 'text', 'label' => 'Tid', 'default' => '', 'max' => 30],
                ],
                'default' => [$day('Mandag'), $day('Tirsdag'), $day('Onsdag'), $day('Torsdag')],
            ],
            'links_title' => [
                'type'    => 'text',
                'label'   => 'Kolonne 2: overskrift',
                'default' => 'Sider',
            ],
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Kolonne 2: links',
                'max_rows' => 12,
                'fields'   => BridgeCardKit::linkRowFields(),
                'default'  => array_fill(0, 9, $link),
            ],
            'info_title' => [
                'type'    => 'text',
                'label'   => 'Kolonne 3: overskrift',
                'default' => 'Information',
            ],
            'info' => [
                'type'     => 'repeater',
                'label'    => 'Kolonne 3: linjer',
                'max_rows' => 8,
                'fields'   => [
                    'text' => ['type' => 'text', 'label' => 'Tekst', 'default' => '', 'max' => 120],
                ],
                'default' => [
                    ['text' => 'charlottelund-bridgeclub'],
                    ['text' => '+45 23868124'],
                    ['text' => 'Charlotte lund 4560 4'],
                    ['text' => 'info@klub.dk'],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        $c = BridgeCardKit::COLORS;

        return [
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => $c['dark'],
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Overskrifter',
                'default' => $c['blue'],
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => $c['teal_light'],
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        return static::renderTemplate([
            'timesTitle' => (string) ($settings['times_title'] ?? ''),
            'times'      => BridgeCardKit::rows($settings['times'] ?? []),
            'linksTitle' => (string) ($settings['links_title'] ?? ''),
            'links'      => BridgeCardKit::links($settings['links'] ?? [], $context),
            'infoTitle'  => (string) ($settings['info_title'] ?? ''),
            'info'       => BridgeCardKit::rows($settings['info'] ?? []),
            'cssVars'    => static::cssVariables($styles),
            'context'    => $context,
        ]);
    }
}
