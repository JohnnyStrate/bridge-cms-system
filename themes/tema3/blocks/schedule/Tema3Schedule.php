<?php
declare(strict_types=1);

/**
 * Tider og steder til tema 3 — fx "SPIL BRIDGE".
 *
 * Øverst samme titellinje som "Info med billede":  >  TITEL  MINITITEL  <
 * Under den en række punkter. Hvert punkt har en overskrift til venstre
 * og en rød gradient-boks til højre med et lille link under.
 *
 * Holder man musen over et link, bliver linjen under det til en pil med
 * en lille cirkel foran, og punktets overskrift til venstre bliver kursiv.
 *
 * Ingen baggrund — sektionen ligger direkte på sidens baggrund.
 */
final class Tema3ScheduleBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-schedule';
    }

    public static function label(): string
    {
        return 'Tider og steder — tema 3';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Spil bridge',
                'max'     => 60,
            ],
            'eyebrow' => [
                'type'    => 'text',
                'label'   => 'Minititel (til højre for overskriften)',
                'default' => 'Vores spillehold',
                'max'     => 30,
            ],
            'items' => [
                'type'     => 'repeater',
                'label'    => 'Punkter (overskrift, tekst, link)',
                'max_rows' => 6,
                'fields'   => [
                    'title' => [
                        'type'        => 'text',
                        'label'       => 'Overskrift',
                        'placeholder' => 'Fx Klubaften',
                        'default'     => '',
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst i boksen',
                        'default' => '',
                        'max'     => 300,
                    ],
                    'link_label' => [
                        'type'        => 'text',
                        'label'       => 'Link: tekst (tom = intet link)',
                        'placeholder' => 'Fx Se program',
                        'default'     => '',
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Link: side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Link: ekstern adresse',
                        'placeholder' => 'Indsæt link',
                        'default'     => '',
                    ],
                ],
                'default' => [
                    [
                        'title'      => 'Klubaften',
                        'text'       => 'Skriv hvilken ugedag og hvilket klokkeslæt I spiller, fx mandag kl. 18.45.',
                        'link_label' => 'Se program',
                        'page'       => 0,
                        'url'        => '#',
                    ],
                    [
                        'title'      => 'Åbent hus',
                        'text'       => 'Fortæl hvor I mødes — adresse, lokale og hvordan man finder vej.',
                        'link_label' => 'Se adresse',
                        'page'       => 0,
                        'url'        => '#',
                    ],
                    [
                        'title'      => 'Sommer- og sølvturneringer',
                        'text'       => 'Skriv om sæsonens turneringer, og hvornår man skal tilmelde sig.',
                        'link_label' => 'Se turneringer',
                        'page'       => 0,
                        'url'        => '#',
                    ],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'arrow_color' => [
                'type'    => 'color',
                'label'   => 'Pile',
                'default' => '#733c34',
                'group'   => 'Overskrift',
            ],
            'title_start' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til venstre',
                'default' => '#9f9a99',
                'group'   => 'Overskrift',
            ],
            'title_end' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til højre',
                'default' => '#bf6356',
                'group'   => 'Overskrift',
            ],
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Minititel',
                'default' => '#c0c0c0',
                'group'   => 'Overskrift',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 64,
                'min'     => 28,
                'max'     => 120,
                'unit'    => 'px',
                'group'   => 'Overskrift',
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Punkternes overskrifter',
                'default' => '#4d4848',
                'group'   => 'Punkter',
            ],
            'heading_size' => [
                'type'    => 'number',
                'label'   => 'Punkternes overskrifter: størrelse',
                'default' => 34,
                'min'     => 18,
                'max'     => 64,
                'unit'    => 'px',
                'group'   => 'Punkter',
            ],
            'box_start' => [
                'type'    => 'color',
                'label'   => 'Boks: farve til venstre',
                'default' => '#ea5b5b',
                'group'   => 'Punkter',
            ],
            'box_end' => [
                'type'    => 'color',
                'label'   => 'Boks: farve til højre',
                'default' => '#ce9c9c',
                'group'   => 'Punkter',
            ],
            'box_text' => [
                'type'    => 'color',
                'label'   => 'Tekst i boksen',
                'default' => '#ffffff',
                'group'   => 'Punkter',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekst i boksen: størrelse',
                'default' => 16,
                'min'     => 12,
                'max'     => 28,
                'unit'    => 'px',
                'group'   => 'Punkter',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Boks: afrunding',
                'default' => 14,
                'min'     => 0,
                'max'     => 40,
                'unit'    => 'px',
                'group'   => 'Punkter',
            ],
            'link_color' => [
                'type'    => 'color',
                'label'   => 'Link',
                'default' => '#828282',
                'group'   => 'Links',
            ],
            'link_hover' => [
                'type'    => 'color',
                'label'   => 'Link ved hover',
                'default' => '#555151',
                'group'   => 'Links',
            ],
            'line_color' => [
                'type'    => 'color',
                'label'   => 'Linje og pil',
                'default' => '#dd6060',
                'group'   => 'Links',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Links',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing = $context->isInlineEditing();
        $items   = [];

        foreach (array_values((array) ($settings['items'] ?? [])) as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text  = trim((string) ($item['text'] ?? ''));

            // Et helt tomt punkt vises ikke på siden — men i editoren, så
            // man kan klikke i det og skrive.
            if ($title === '' && $text === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($item['page'] ?? 0);
            $url    = (string) ($item['url'] ?? '');

            $items[] = [
                'index'     => $index,
                'title'     => $title,
                'text'      => $text,
                'linkLabel' => trim((string) ($item['link_label'] ?? '')),
                'href'      => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        return static::renderTemplate([
            'title'   => (string) ($settings['title'] ?? ''),
            'eyebrow' => trim((string) ($settings['eyebrow'] ?? '')),
            'items'   => $items,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
