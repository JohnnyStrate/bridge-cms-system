<?php
declare(strict_types=1);

/**
 * Navbar: menupunkter i midten og et logo til højre.
 *
 * Står navbaren lige før heroen, ligger den gennemsigtigt oven på billedet.
 * På alle andre sider (og i editoren) har den sin egen farve.
 */
final class StandardBridgeNavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-navbar';
    }

    public static function label(): string
    {
        return 'Navbar';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        $link = static fn (string $label): array => ['label' => $label, 'page' => 0, 'url' => '#'];

        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter (tekst, side, adresse)',
                'max_rows' => 10,
                'fields'   => [
                    'label' => ['type' => 'text', 'label' => 'Tekst', 'default' => ''],
                    'page'  => ['type' => 'page', 'label' => 'Side', 'default' => 0],
                    'url'   => ['type' => 'url', 'label' => 'Ekstern adresse', 'default' => ''],
                ],
                'default' => [
                    $link('Forside'),
                    $link('Klubber'),
                    $link('Turneringer & tilmelding'),
                    $link('BC3'),
                    $link('Resultater'),
                    $link('Bridgebutikken'),
                ],
            ],
            'logo' => [
                'type'    => 'image',
                'label'   => 'Logo til højre',
                'default' => 'themes/standard-bridge/assets/logostandard.png',
            ],
            'logo_alt' => [
                'type'    => 'text',
                'label'   => 'Logo: beskrivelse (alt-tekst)',
                'default' => 'Danmarks Bridgeforbund',
            ],
            'logo_url' => [
                'type'        => 'url',
                'label'       => 'Logo: link',
                'placeholder' => 'Tom = intet link',
                'default'     => '',
            ],
        ];
    }

    // UDSEENDE — bliver til CSS-variabler i block.css.
    public static function getStyleSchema(): array
    {
        return [
            // Bruges, når navbaren IKKE ligger oven på heroen (og i editoren).
            'box_color' => ['type' => 'color', 'label' => 'Baggrund', 'default' => '#2b2b2b'],
            'text_color' => ['type' => 'color', 'label' => 'Tekst', 'default' => '#ffffff'],
            'link_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 16,
                'min'     => 12,
                'max'     => 26,
                'unit'    => 'px',
            ],
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        $current = $context->currentPageId();
        $links   = [];
        $linked  = false;

        foreach (array_values((array) $settings['links']) as $index => $link) {
            if (!is_array($link)) {
                continue;
            }

            $label = trim((string) ($link['label'] ?? ''));

            // Et menupunkt uden tekst vises kun i editoren, så det kan udfyldes.
            if ($label === '' && !$context->isInlineEditing()) {
                continue;
            }

            $pageId = (int) ($link['page'] ?? 0);
            $url    = (string) ($link['url'] ?? '');
            $linked = $linked || $pageId > 0;

            $links[] = [
                'index'  => $index,
                'label'  => $label,
                // En valgt side vinder over en skrevet adresse.
                'href'   => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
                // Den side, man står på, vises med fed.
                'active' => $pageId > 0 && $pageId === $current,
            ];
        }

        // Er ingen menupunkter koblet til en side endnu, vises det første med
        // fed, så man kan se, hvordan den aktive side ser ud.
        if (!$linked && $links !== []) {
            $links[0]['active'] = true;
        }

        return static::renderTemplate([
            'links'   => $links,
            'logo'    => $context->asset((string) $settings['logo']),
            'logoAlt' => (string) $settings['logo_alt'],
            'logoUrl' => (string) $settings['logo_url'],
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
