<?php
declare(strict_types=1);

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

    // INDHOLD — kun ÉN getSchema(), alle felter i samme array.
    public static function getSchema(): array
    {
        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter',
                'max_rows' => 30,
                'fields'   => [
                    'label' => ['type' => 'text', 'label' => 'Tekst', 'default' => ''],
                    'page'  => ['type' => 'page', 'label' => 'Side', 'default' => 0],
                    'url'   => ['type' => 'url', 'label' => 'Ekstern adresse', 'default' => ''],
                ],
                'default' => [
                    ['label' => 'Forside', 'page' => 0, 'url' => '#'],
                    ['label' => 'Om klubben', 'page' => 0, 'url' => '#'],
                    ['label' => 'Turnering', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'cta_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Bliv medlem',
            ],
            'cta_page' => [
                'type'    => 'page',
                'label'   => 'Knap: side',
                'default' => 0,
            ],
            'cta_url' => [
                'type'        => 'url',
                'label'       => 'Knap: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
        ];
    }

    // UDSEENDE — box_color bliver til --box-color i block.css
    public static function getStyleSchema(): array
    {
        return [
            // Bruges, når navbaren IKKE ligger oven på heroen (og i editoren).
            // Oven på heroen er den gennemsigtig — se block.css.
            'box_color' => ['type' => 'color', 'label' => 'Boksens farve', 'default' => '#2b2b2b'],
        ];
    }

    // Sender felterne videre til template.php som variabler.
    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        return static::renderTemplate([
            'links'    => $settings['links'],
            'ctaLabel' => $settings['cta_label'],
            'ctaHref'  => $settings['cta_page'] > 0 ? $context->pageUrl($settings['cta_page']) : $settings['cta_url'],
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
        ]);
    }
}