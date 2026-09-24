<?php
declare(strict_types=1);

/**
 * Hero til tema 2.
 *
 * Fuldbredde-foto med et blåt filter, stor overskrift, en kort tekst med
 * en knap ved siden af og en bølget underkant.
 *
 * Filteret er en farvet flade oven på billedet med en gennemsigtighed —
 * samme som i Figma (#174765 ved 40 %). Begge kan ændres i editoren.
 *
 * Overskriften står lige under venstre kant af tema 2's navbar, fordi de
 * to blokke deler kolonner (se block.css).
 */
final class Tema2HeroBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-hero';
    }

    public static function label(): string
    {
        return 'Hero — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Din Bridgeklub',
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'Skriv et par linjer om klubben her - hvor I spiller, og hvilke dage I mødes.',
                'max'     => 400,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Bliv medlem',
                'max'         => 40,
            ],
            'button_page' => [
                'type'    => 'page',
                'label'   => 'Knap: side',
                'default' => 0,
            ],
            'button_url' => [
                'type'        => 'url',
                'label'       => 'Knap: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede',
                'default' => 'themes/tema2/assets/heroimage.png',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'overlay_color' => [
                'type'    => 'color',
                'label'   => 'Filter over billedet',
                'default' => '#174765',
                'group'   => 'Billede',
            ],
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Filterets styrke',
                'default' => 40,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'wave_color' => [
                'type'    => 'color',
                'label'   => 'Bølgen (farven under hero)',
                'default' => '#ffffff',
                'group'   => 'Billede',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 96,
                'min'     => 32,
                'max'     => 160,
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
            'radius' => [
                'type'    => 'number',
                'label'   => 'Knap: afrunding',
                'default' => 12,
                'min'     => 0,
                'max'     => 40,
                'unit'    => 'px',
                'group'   => 'Knap',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $label  = trim((string) ($settings['button_label'] ?? ''));
        $pageId = (int) ($settings['button_page'] ?? 0);
        $url    = (string) ($settings['button_url'] ?? '');

        // Side før adresse, ligesom i navbaren.
        $href = $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#');

        return static::renderTemplate([
            'title'       => (string) ($settings['title'] ?? ''),
            'text'        => (string) ($settings['text'] ?? ''),
            'buttonLabel' => $label,
            'buttonHref'  => $href,
            'bgImage'     => $context->asset((string) ($settings['bg_image'] ?? '')),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
