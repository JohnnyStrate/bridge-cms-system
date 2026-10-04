<?php
declare(strict_types=1);

/**
 * Hero: stort billede med overskrift, en lille linje tekst og to knapper
 * nederst til venstre.
 *
 * Knap 1 er den lyse med kant ("Kontakt"). Knap 2 er den mørke ("Tilmeld"),
 * som får hvid tekst og en pil, når musen er over den.
 */
final class StandardBridgeHero extends AbstractBlock
{
    public static function type(): string
    {
        return 'standardbridge-hero';
    }

    public static function label(): string
    {
        return 'Hero — standardbridge';
    }

    // INDHOLD
    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'textarea',
                'label'   => 'Overskrift (Enter = ny linje)',
                'default' => "Din\nBridgeklub.",
                'max'     => 120,
            ],
            'tagline' => [
                'type'    => 'text',
                'label'   => 'Lille tekst under overskriften',
                'default' => 'Der skal stå et eller andet her',
            ],
            'btn1_label' => [
                'type'        => 'text',
                'label'       => 'Knap 1: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Kontakt',
            ],
            'btn1_page' => ['type' => 'page', 'label' => 'Knap 1: side', 'default' => 0],
            'btn1_url'  => ['type' => 'url', 'label' => 'Knap 1: ekstern adresse', 'default' => ''],
            'btn2_label' => [
                'type'        => 'text',
                'label'       => 'Knap 2: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld',
            ],
            'btn2_page' => ['type' => 'page', 'label' => 'Knap 2: side', 'default' => 0],
            'btn2_url'  => ['type' => 'url', 'label' => 'Knap 2: ekstern adresse', 'default' => ''],
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede',
                'default' => 'themes/standard-bridge/assets/bg-card.png',
            ],
        ];
    }

    // UDSEENDE — hvert felt bliver til en CSS-variabel i block.css:
    // text_color → --text-color, button_color → --button-color osv.
    public static function getStyleSchema(): array
    {
        return [
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 64,
                'min'     => 28,
                'max'     => 140,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap 2: farve',
                'default' => '#3d3a19',
                'group'   => 'Knapper',
            ],
            'overlay' => [
                'type'    => 'number',
                'label'   => 'Mørkt lag over billedet',
                'default' => 0,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            // Auto = højden følger skærmens bredde (se block.css).
            ...static::boxStyleFields('hero', 'Størrelse', ['height']),
        ];
    }

    public static function render(array $settings, array $styles, RenderContext $context): string
    {
        return static::renderTemplate([
            'title'     => (string) $settings['title'],
            'tagline'   => (string) $settings['tagline'],
            'btn1Label' => trim((string) $settings['btn1_label']),
            'btn1Href'  => self::href((int) $settings['btn1_page'], (string) $settings['btn1_url'], $context),
            'btn2Label' => trim((string) $settings['btn2_label']),
            'btn2Href'  => self::href((int) $settings['btn2_page'], (string) $settings['btn2_url'], $context),
            'bgImage'   => $context->asset((string) $settings['bg_image']),
            'cssVars'   => static::cssVariables($styles),
            'context'   => $context,
        ]);
    }

    // En valgt side vinder over en skrevet adresse. Intet valgt = '#'.
    private static function href(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }
}