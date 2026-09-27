<?php
declare(strict_types=1);

/**
 * Linkkort til tema 3 — fx links til forbundets mesterpoint, rangliste osv.
 *
 * En rød gradient-boks fra kant til kant. Øverst til venstre i boksen:
 * samme titellinje som i de andre tema 3-sektioner (>  TITEL  MINITITEL  <),
 * men i hvidt. Under den en række hvide kort med overskrift, en tekst med
 * en lille prik foran og en knap nederst.
 *
 * Sektionen har ingen luft over sig, så den sidder lige under
 * billedkarrusellen.
 */
final class Tema3LinksBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-links';
    }

    public static function label(): string
    {
        return 'Linkkort — tema 3';
    }

    public static function getSchema(): array
    {
        $card = static function (string $title, string $text): array {
            return [
                'title'        => $title,
                'text'         => $text,
                'button_label' => 'Klik her',
                'page'         => 0,
                'url'          => '#',
            ];
        };

        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Bridge i din by',
                'max'     => 60,
            ],
            'eyebrow' => [
                'type'    => 'text',
                'label'   => 'Minititel (til højre for overskriften)',
                'default' => 'Bridgeforbund',
                'max'     => 30,
            ],
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort (overskrift, tekst, knap)',
                'max_rows' => 8,
                'fields'   => [
                    'title' => [
                        'type'        => 'text',
                        'label'       => 'Overskrift',
                        'placeholder' => 'Fx Rangliste',
                        'default'     => '',
                    ],
                    'text' => [
                        'type'    => 'textarea',
                        'label'   => 'Tekst',
                        'default' => '',
                        'max'     => 400,
                    ],
                    'button_label' => [
                        'type'        => 'text',
                        'label'       => 'Knap: tekst (tom = ingen knap)',
                        'placeholder' => 'Fx Klik her',
                        'default'     => '',
                        'max'         => 30,
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Knap: side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Knap: ekstern adresse',
                        'placeholder' => 'Indsæt link',
                        'default'     => '',
                    ],
                ],
                'default' => [
                    $card('Mesterpoint', 'Skriv kort, hvad man finder bag linket — fx klubbens medlemmer fordelt efter mesterpoint.'),
                    $card('Rangliste', 'Fortæl, hvad ranglisten viser, og hvor ofte den bliver opdateret.'),
                    $card('Bronzestilling', 'Beskriv stillingen — fx sæsonens bronzepoint for klubbens spillere.'),
                    $card('Handicap', 'Forklar kort, hvad handicap betyder, og hvor man kan se sit eget.'),
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
                'default' => '#ffffff',
                'group'   => 'Overskrift',
            ],
            'title_end' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til højre',
                'default' => '#e8e8e8',
                'group'   => 'Overskrift',
            ],
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Minititel',
                'default' => '#e6e6e6',
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
            'box_start' => [
                'type'    => 'color',
                'label'   => 'Boks: farve til venstre',
                'default' => '#c26c6c',
                'group'   => 'Boks',
            ],
            'box_end' => [
                'type'    => 'color',
                'label'   => 'Boks: farve til højre',
                'default' => '#d48989',
                'group'   => 'Boks',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Kort',
                'default' => '#ffffff',
                'group'   => 'Kort',
            ],
            'card_title_color' => [
                'type'    => 'color',
                'label'   => 'Kort: overskrift',
                'default' => '#3b3b3b',
                'group'   => 'Kort',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Kort: tekst',
                'default' => '#464343',
                'group'   => 'Kort',
            ],
            'bullet_color' => [
                'type'    => 'color',
                'label'   => 'Kort: prik foran teksten',
                'default' => '#d86f6f',
                'group'   => 'Kort',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Kort: afrunding',
                'default' => 6,
                'min'     => 0,
                'max'     => 40,
                'unit'    => 'px',
                'group'   => 'Kort',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#8d8d8d',
                'group'   => 'Knapper',
            ],
            'button_hover' => [
                'type'    => 'color',
                'label'   => 'Knap ved hover',
                'default' => '#e36d6d',
                'group'   => 'Knapper',
            ],
            'button_text' => [
                'type'    => 'color',
                'label'   => 'Knaptekst',
                'default' => '#ffffff',
                'group'   => 'Knapper',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Knapper',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing = $context->isInlineEditing();
        $cards   = [];

        foreach (array_values((array) ($settings['cards'] ?? [])) as $index => $card) {
            if (!is_array($card)) {
                continue;
            }

            $title = trim((string) ($card['title'] ?? ''));
            $text  = trim((string) ($card['text'] ?? ''));

            if ($title === '' && $text === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($card['page'] ?? 0);
            $url    = (string) ($card['url'] ?? '');

            $cards[] = [
                'index'       => $index,
                'title'       => $title,
                'text'        => $text,
                'buttonLabel' => trim((string) ($card['button_label'] ?? '')),
                'href'        => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        return static::renderTemplate([
            'title'   => (string) ($settings['title'] ?? ''),
            'eyebrow' => trim((string) ($settings['eyebrow'] ?? '')),
            'cards'   => $cards,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
