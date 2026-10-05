<?php
declare(strict_types=1);

/**
 * Billedbanner med link til Standard Bridge 2 — fx "Flere nyheder ↗".
 *
 * Et bredt, lavt billede i samme bredde som sektionerne over. Teksten
 * står til venstre på en mørk toning, der går fra venstre og ud i
 * billedet. Hele banneret er ét link.
 *
 * Mørket er lavet i CSS (en sort overlay + en sort gradient fra venstre)
 * i stedet for en separat blackgradient.png: det er én fil mindre at
 * hente, det skalerer til alle skærmbredder, og styrken kan skrues på i
 * stil-panelet.
 */
final class Standard2BannerBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-banner';
    }

    public static function label(): string
    {
        return 'Billedbanner med link — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'label' => [
                'type'    => 'text',
                'label'   => 'Tekst',
                'default' => 'Flere nyheder',
                'max'     => 60,
            ],
            'page' => [
                'type'    => 'page',
                'label'   => 'Side',
                'default' => 0,
            ],
            'url' => [
                'type'        => 'url',
                'label'       => 'Ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede',
                'default' => 'themes/standard2/assets/nyheder2.jpg',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Sort overlay over hele billedet',
                'default' => 26,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'shade_opacity' => [
                'type'    => 'number',
                'label'   => 'Mørk toning fra venstre (bag teksten)',
                'default' => 40,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'image_position' => [
                'type'    => 'number',
                'label'   => 'Billedets placering (0 = top, 100 = bund)',
                'default' => 50,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'height' => [
                'type'    => 'number',
                'label'   => 'Højde',
                'default' => 150,
                'min'     => 90,
                'max'     => 400,
                'unit'    => 'px',
                'group'   => 'Billede',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Tekst',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 40,
                'min'     => 18,
                'max'     => 72,
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
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $pageId = (int) ($settings['page'] ?? 0);
        $url    = (string) ($settings['url'] ?? '');
        $image  = (string) ($settings['image'] ?? '');

        return static::renderTemplate([
            'label'   => (string) ($settings['label'] ?? ''),
            'href'    => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            'image'   => $image !== '' ? $context->asset($image) : '',
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
