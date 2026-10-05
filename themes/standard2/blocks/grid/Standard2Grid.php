<?php
declare(strict_types=1);

/**
 * Billedgitter til Standard Bridge 2 — fx "Mesterpoint og klubstillinger".
 *
 * Temaets sektionsoverskrift og en kort tekst. Under dem store billedlinks
 * to og to, der tilsammen er præcis så brede som overskriftsboksen.
 * Hvert billede har kun en tekst med en skrå pil ("Se rangliste ↗") — samme
 * udseende og hover som "Flere nyheder".
 *
 * Der er bevidst ingen ikoner: teksten og pilen siger allerede, hvad
 * billedet fører til, og temaet er bygget på få, rolige virkemidler.
 */
final class Standard2GridBlock extends AbstractBlock
{
    public const MAX_TILES = 6;

    public static function type(): string
    {
        return 'standard2-grid';
    }

    public static function label(): string
    {
        return 'Billedgitter — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        $tile = static fn (string $label, string $image): array => [
            'label' => $label,
            'page'  => 0,
            'url'   => '#',
            'image' => 'themes/standard2/assets/' . $image,
        ];

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Mesterpoint og klubstillinger',
                'max'     => 60,
            ],
            'intro' => [
                'type'    => 'textarea',
                'label'   => 'Kort tekst under overskriften (tom = ingen)',
                'default' => 'Følg med i klubbens stillinger — se mesterpoint, rangliste, bronzestilling '
                    . 'og handicap direkte i forbundets systemer.',
                'max'     => 500,
            ],
            'tiles' => [
                'type'     => 'repeater',
                'label'    => 'Billeder (højst ' . self::MAX_TILES . ', to pr. række)',
                'max_rows' => self::MAX_TILES,
                'fields'   => [
                    'label' => [
                        'type'        => 'text',
                        'label'       => 'Tekst på billedet',
                        'placeholder' => 'Fx Se rangliste',
                        'default'     => '',
                        'max'         => 50,
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Linker til: side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Linker til: ekstern adresse',
                        'placeholder' => 'Indsæt link',
                        'default'     => '',
                    ],
                    'image' => [
                        'type'    => 'image',
                        'label'   => 'Billede',
                        'default' => '',
                    ],
                ],
                'default' => [
                    $tile('Se mesterpoint', 'mesterpoint.jpg'),
                    $tile('Se rangliste', 'rangliste.jpg'),
                    $tile('Se bronzestilling', 'bronzestilling.jpg'),
                    $tile('Se handicapliste', 'handicapliste.jpg'),
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'heading_style' => [
                'type'    => 'select',
                'label'   => 'Overskriftens udseende',
                'default' => 'Boks',
                'options' => Standard2Kit::HEADING_STYLES,
                'group'   => 'Overskrift',
            ],
            'heading_bg' => [
                'type'    => 'color',
                'label'   => 'Boks: streg og baggrund',
                'default' => '#5b5959',
                'group'   => 'Overskrift',
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Boks: tekst',
                'default' => '#ffffff',
                'group'   => 'Overskrift',
            ],
            'plain_color' => [
                'type'    => 'color',
                'label'   => 'Stor tekst: farve',
                'default' => '#454545',
                'group'   => 'Overskrift',
            ],
            'heading_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 64,
                'min'     => 28,
                'max'     => 110,
                'unit'    => 'px',
                'group'   => 'Overskrift',
            ],
            'intro_color' => [
                'type'    => 'color',
                'label'   => 'Kort tekst',
                'default' => '#635e5e',
                'group'   => 'Tekst',
            ],
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Sort overlay over billederne',
                'default' => 40,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billeder',
            ],
            'shade_opacity' => [
                'type'    => 'number',
                'label'   => 'Mørk toning fra venstre',
                'default' => 0,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billeder',
            ],
            'tile_ratio' => [
                'type'    => 'number',
                'label'   => 'Billedernes højde (i forhold til bredden — 100 = kvadrat)',
                'default' => 54,
                'min'     => 30,
                'max'     => 100,
                'group'   => 'Billeder',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekst på billederne: størrelse',
                'default' => 40,
                'min'     => 18,
                'max'     => 72,
                'unit'    => 'px',
                'group'   => 'Billeder',
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
        $editing = $context->isInlineEditing();
        $tiles   = [];

        foreach (array_values((array) ($settings['tiles'] ?? [])) as $index => $tile) {
            if (!is_array($tile) || count($tiles) >= self::MAX_TILES) {
                continue;
            }

            $label = trim((string) ($tile['label'] ?? ''));
            $image = (string) ($tile['image'] ?? '');

            if ($label === '' && $image === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($tile['page'] ?? 0);
            $url    = (string) ($tile['url'] ?? '');

            $tiles[] = [
                'index' => $index,
                'label' => $label,
                'href'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
                'image' => $image !== '' ? $context->asset($image) : '',
            ];
        }

        return static::renderTemplate([
            'title'        => (string) ($settings['title'] ?? ''),
            'intro'        => (string) ($settings['intro'] ?? ''),
            'headingStyle' => (string) ($styles['heading_style'] ?? 'Boks'),
            'tiles'        => $tiles,
            'cssVars'      => static::cssVariables($styles),
            'context'      => $context,
        ]);
    }
}
