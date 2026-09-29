<?php
declare(strict_types=1);

/**
 * Velkommen + seneste nyt til Standard Bridge 2.
 *
 * Øverst temaets sektionsoverskrift (streg + mørk boks med "Velkommen"),
 * under den en kort, centreret tekst. Et godt stykke længere nede
 * "Seneste nyt!" og en hvid boks med op til tre korte opslag fra klubben,
 * adskilt af tynde, lodrette streger.
 *
 * Alt går præcis ud til samme bredde som overskriftsboksen (--s2-width).
 */
final class Standard2WelcomeBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-welcome';
    }

    public static function label(): string
    {
        return 'Velkommen og nyt — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Velkommen',
                'max'     => 60,
            ],
            'intro' => [
                'type'    => 'textarea',
                'label'   => 'Kort tekst under overskriften',
                'default' => 'Skriv et par linjer om klubben her — hvor I spiller, hvilke dage I mødes, '
                    . 'og om der er undervisning for nye spillere.',
                'max'     => 500,
            ],
            'news_label' => [
                'type'        => 'text',
                'label'       => 'Lille overskrift over opslagene',
                'placeholder' => 'Tom = ingen opslag',
                'default'     => 'Seneste nyt!',
                'max'         => 40,
            ],
            'news' => [
                'type'     => 'repeater',
                'label'    => 'Opslag (højst 3)',
                'max_rows' => 3,
                'fields'   => [
                    'title' => [
                        'type'        => 'text',
                        'label'       => 'Overskrift',
                        'placeholder' => 'Fx Mandag aften',
                        'default'     => '',
                        'max'         => 60,
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst',
                        'default' => '',
                        'max'     => 600,
                    ],
                ],
                'default' => [
                    [
                        'title' => 'Mandag aften',
                        'text'  => 'Skriv en kort nyhed fra klubben her — fx en ændring i spilletiden, '
                            . 'en ny turnering eller et resultat, I er stolte af.',
                    ],
                    [
                        'title' => 'Åbent hus',
                        'text'  => 'Brug boksen til det, medlemmerne skal vide lige nu. Hold teksten '
                            . 'kort — et par linjer er nok.',
                    ],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'heading_bg' => [
                'type'    => 'color',
                'label'   => 'Overskrift: streg og boks',
                'default' => '#5b5959',
                'group'   => 'Overskrift',
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift: tekst',
                'default' => '#ffffff',
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
            'news_color' => [
                'type'    => 'color',
                'label'   => 'Opslag: tekst',
                'default' => '#353535',
                'group'   => 'Opslag',
            ],
            'divider_color' => [
                'type'    => 'color',
                'label'   => 'Opslag: lodret streg',
                'default' => '#ebe8e8',
                'group'   => 'Opslag',
            ],
            'box_color' => [
                'type'    => 'color',
                'label'   => 'Opslag: boks',
                'default' => '#ffffff',
                'group'   => 'Opslag',
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
        $news    = [];

        foreach (array_values((array) ($settings['news'] ?? [])) as $index => $item) {
            if (!is_array($item) || count($news) >= 3) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text  = trim((string) ($item['text'] ?? ''));

            if ($title === '' && $text === '' && !$editing) {
                continue;
            }

            $news[] = ['index' => $index, 'title' => $title, 'text' => $text];
        }

        return static::renderTemplate([
            'title'     => (string) ($settings['title'] ?? ''),
            'intro'     => (string) ($settings['intro'] ?? ''),
            'newsLabel' => trim((string) ($settings['news_label'] ?? '')),
            'news'      => $news,
            'cssVars'   => static::cssVariables($styles),
            'context'   => $context,
        ]);
    }
}
