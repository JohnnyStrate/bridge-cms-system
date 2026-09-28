<?php
declare(strict_types=1);

/**
 * Billedkarrusel til tema 3.
 *
 * Til venstre en lysegrå boks, der går fra skærmens venstre kant til
 * midten. I den står billederne ét ad gangen i en diamantform med tyk hvid
 * kant. Under billedet: pil, prikker (én pr. billede), pil. Øverst i
 * højre hjørne står nummeret på det billede, man ser.
 *
 * Til højre, direkte på sidens baggrund, står billedets tekst. Den glider
 * ind, når man skifter billede.
 *
 * Der kan være op til MAX_SLIDES billeder. Flere prikker end det bliver
 * uoverskueligt — og siden bliver tung at hente.
 *
 * Karrusellen styres af et lille script i template.php. Uden script (eller
 * i editoren) vises det første billede.
 */
final class Tema3SliderBlock extends AbstractBlock
{
    public const MAX_SLIDES = 6;

    public static function type(): string
    {
        return 'tema3-slider';
    }

    public static function label(): string
    {
        return 'Billedkarrusel — tema 3';
    }

    public static function getSchema(): array
    {
        return [
            'slides' => [
                'type'     => 'repeater',
                'label'    => 'Billeder (højst ' . self::MAX_SLIDES . ')',
                'max_rows' => self::MAX_SLIDES,
                'fields'   => [
                    'image' => [
                        'type'    => 'image',
                        'label'   => 'Billede',
                        'default' => '',
                    ],
                    'alt' => [
                        'type'        => 'text',
                        'label'       => 'Beskrivelse af billedet (alt-tekst)',
                        'placeholder' => 'Hvad viser billedet?',
                        'default'     => '',
                    ],
                    'caption' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst ved siden af billedet',
                        'default' => '',
                        'max'     => 500,
                    ],
                ],
                'default' => [
                    [
                        'image'   => 'themes/tema2/assets/welcomeimage.jpg',
                        'alt'     => 'Spillere ved et bord',
                        'caption' => 'Skriv et par linjer om billedet — fx hvad der sker på klubaftenerne, og hvem der kan være med.',
                    ],
                    [
                        'image'   => 'themes/tema2/assets/heroimage.jpg',
                        'alt'     => 'Et spillekort — klør es — på et træbord',
                        'caption' => 'Fortæl om åbent hus: hvornår I har åbent, og om der er undervisning før spillestart.',
                    ],
                    [
                        'image'   => 'themes/tema2/assets/welcomeimage.jpg',
                        'alt'     => 'Spillere ved et bord',
                        'caption' => 'Her kan I skrive om turneringer, sociale arrangementer eller noget helt tredje.',
                    ],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'box_color' => [
                'type'    => 'color',
                'label'   => 'Boks',
                'default' => '#eeeeee',
                'group'   => 'Boks',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Boks: afrunding',
                'default' => 16,
                'min'     => 0,
                'max'     => 60,
                'unit'    => 'px',
                'group'   => 'Boks',
            ],
            'counter_color' => [
                'type'    => 'color',
                'label'   => 'Tallet i hjørnet',
                'default' => '#cc7c7c',
                'group'   => 'Boks',
            ],
            'stroke_color' => [
                'type'    => 'color',
                'label'   => 'Kant om billedet',
                'default' => '#ffffff',
                'group'   => 'Billede',
            ],
            'stroke_width' => [
                'type'    => 'number',
                'label'   => 'Kantens tykkelse',
                'default' => 12,
                'min'     => 0,
                'max'     => 30,
                'unit'    => 'px',
                'group'   => 'Billede',
            ],
            'dot_color' => [
                'type'    => 'color',
                'label'   => 'Prikker',
                'default' => '#e5e5e5',
                'group'   => 'Navigation',
            ],
            'dot_active' => [
                'type'    => 'color',
                'label'   => 'Aktiv prik og streg',
                'default' => '#e36d6d',
                'group'   => 'Navigation',
            ],
            'arrow_color' => [
                'type'    => 'color',
                'label'   => 'Pile',
                'default' => '#e36d6d',
                'group'   => 'Navigation',
            ],
            'caption_color' => [
                'type'    => 'color',
                'label'   => 'Tekst til højre',
                'default' => '#000000',
                'group'   => 'Tekst',
            ],
            'caption_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 22,
                'min'     => 14,
                'max'     => 36,
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
        $editing = $context->isInlineEditing();
        $slides  = [];

        foreach (array_values((array) ($settings['slides'] ?? [])) as $index => $slide) {
            if (!is_array($slide) || count($slides) >= self::MAX_SLIDES) {
                continue;
            }

            $image   = (string) ($slide['image'] ?? '');
            $caption = trim((string) ($slide['caption'] ?? ''));

            // En række uden billede springes over på siden — men står i
            // editoren, så man kan vælge et billede til den.
            if ($image === '' && !$editing) {
                continue;
            }

            $slides[] = [
                'index'   => $index,
                'image'   => $image !== '' ? $context->asset($image) : '',
                'alt'     => (string) ($slide['alt'] ?? ''),
                'caption' => $caption,
            ];
        }

        return static::renderTemplate([
            'slides'  => $slides,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
