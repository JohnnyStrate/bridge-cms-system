<?php
declare(strict_types=1);

/**
 * Navbar til Bridge Card: menupunkter til højre, lagt oven på heroen.
 */
final class BridgeCardNavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'bridgecardtheme-navbar';
    }

    public static function label(): string
    {
        return 'Navbar — Bridge Card';
    }

    public static function getSchema(): array
    {
        $row = static fn (string $label): array => ['label' => $label, 'page' => 0, 'url' => '#'];

        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter',
                'max_rows' => 10,
                'fields'   => BridgeCardKit::linkRowFields(),
                'default'  => [
                    $row('Forside'),
                    $row('Klubber'),
                    $row('Turneringer & tilmelding'),
                    $row('BC3'),
                    $row('Resultater'),
                    $row('Bridgebutikken'),
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => BridgeCardKit::COLORS['white'],
            ],
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => BridgeCardKit::COLORS['dark'],
            ],
            'bg_opacity' => [
                'type'    => 'number',
                'label'   => 'Baggrundens dækkeevne (0 = gennemsigtig)',
                'default' => 0,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
            ],
            'link_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 17,
                'min'     => 12,
                'max'     => 28,
                'unit'    => 'px',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $current = $context->currentPageId();
        $links   = array_map(
            static fn (array $link): array => $link + [
                'active' => $link['page'] > 0 && $link['page'] === $current,
            ],
            BridgeCardKit::links($settings['links'] ?? [], $context)
        );

        return static::renderTemplate([
            'links'   => $links,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
