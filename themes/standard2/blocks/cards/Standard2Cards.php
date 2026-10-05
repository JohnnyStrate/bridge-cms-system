<?php
declare(strict_types=1);

/**
 * Kort med billeder til Standard Bridge 2 — fx "Spil bridge".
 *
 * Øverst temaets sektionsoverskrift og en kort tekst. Under dem op til
 * seks rækker. Hver række er:
 *   - til venstre en hvid boks med en overskrift og en kort tekst,
 *   - til højre et billedlink — samme udseende som "Flere nyheder ↗".
 *
 * Har en række ingen overskrift, står teksten centreret i den hvide boks
 * (som på landingssiden "Turneringer og resultater").
 *
 * Alt går præcis ud til samme bredde som overskriftsboksen (--s2-width).
 */
final class Standard2CardsBlock extends AbstractBlock
{
    public const MAX_ROWS = 6;

    public static function type(): string
    {
        return 'standard2-cards';
    }

    public static function label(): string
    {
        return 'Kort med billeder — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        $row = static function (string $title, string $link, string $image): array {
            return [
                'title'      => $title,
                'text'       => 'Skriv et par linjer her — hvornår I spiller, hvor I mødes, '
                    . 'og hvem der kan være med.',
                'link_label' => $link,
                'page'       => 0,
                'url'        => '#',
                'image'      => $image,
            ];
        };

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Spil bridge',
                'max'     => 60,
            ],
            'intro' => [
                'type'    => 'textarea',
                'label'   => 'Kort tekst under overskriften (tom = ingen)',
                'default' => 'Fortæl kort, hvornår og hvor klubben spiller — og at nye spillere altid er velkomne.',
                'max'     => 500,
            ],
            'rows' => [
                'type'     => 'repeater',
                'label'    => 'Rækker (højst ' . self::MAX_ROWS . ')',
                'max_rows' => self::MAX_ROWS,
                'fields'   => [
                    'title' => [
                        'type'        => 'text',
                        'label'       => 'Overskrift i den hvide boks (tom = kun tekst)',
                        'placeholder' => 'Fx Mandag eftermiddag',
                        'default'     => '',
                        'max'         => 60,
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst i den hvide boks',
                        'default' => '',
                        'max'     => 500,
                    ],
                    'link_label' => [
                        'type'        => 'text',
                        'label'       => 'Tekst på billedet',
                        'placeholder' => 'Fx Se mandag eftermiddag',
                        'default'     => '',
                        'max'         => 50,
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Billedet linker til: side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Billedet linker til: ekstern adresse',
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
                    $row('Mandag eftermiddag', 'Se mandag eftermiddag', 'themes/standard2/assets/mandageftermiddag2.jpg'),
                    $row('Mandag aften / åbent hus', 'Se åbent hus', 'themes/standard2/assets/aabenhust2.jpg'),
                    $row('Sommer- og sølvturneringer', 'Se turneringer', 'themes/standard2/assets/summer.jpg'),
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
            'card_title_color' => [
                'type'    => 'color',
                'label'   => 'Hvid boks: overskrift',
                'default' => '#222121',
                'group'   => 'Hvide bokse',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Hvid boks: tekst',
                'default' => '#635e5e',
                'group'   => 'Hvide bokse',
            ],
            'card_width' => [
                'type'    => 'number',
                'label'   => 'Hvid boks: bredde (del af rækken)',
                'default' => 30,
                'min'     => 20,
                'max'     => 50,
                'unit'    => '%',
                'group'   => 'Hvide bokse',
            ],
            'row_height' => [
                'type'    => 'number',
                'label'   => 'Rækkernes højde',
                'default' => 200,
                'min'     => 120,
                'max'     => 400,
                'unit'    => 'px',
                'group'   => 'Billeder',
            ],
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Sort overlay over billederne',
                'default' => 26,
                'min'     => 0,
                'max'     => 90,
                'unit'    => '%',
                'group'   => 'Billeder',
            ],
            'shade_opacity' => [
                'type'    => 'number',
                'label'   => 'Mørk toning fra venstre (bag teksten)',
                'default' => 40,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
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
        $rows    = [];

        foreach (array_values((array) ($settings['rows'] ?? [])) as $index => $row) {
            if (!is_array($row) || count($rows) >= self::MAX_ROWS) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            $text  = trim((string) ($row['text'] ?? ''));
            $label = trim((string) ($row['link_label'] ?? ''));
            $image = (string) ($row['image'] ?? '');

            if ($title === '' && $text === '' && $label === '' && $image === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($row['page'] ?? 0);
            $url    = (string) ($row['url'] ?? '');

            $rows[] = [
                'index' => $index,
                'title' => $title,
                'text'  => $text,
                'label' => $label,
                'href'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
                'image' => $image !== '' ? $context->asset($image) : '',
            ];
        }

        return static::renderTemplate([
            'title'        => (string) ($settings['title'] ?? ''),
            'intro'        => (string) ($settings['intro'] ?? ''),
            'headingStyle' => (string) ($styles['heading_style'] ?? 'Boks'),
            'rows'         => $rows,
            'cssVars'      => static::cssVariables($styles),
            'context'      => $context,
        ]);
    }
}
