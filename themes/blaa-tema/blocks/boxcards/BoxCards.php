<?php
declare(strict_types=1);

/**
 * Box cards: en overskrift i sin egen bjælke og et antal kort med tekst
 * og en knap.
 *
 * Tre kort er standard, men klubben kan tilføje og fjerne rækker. Er der
 * flere kort, end der er plads til i bredden, bryder de ned i en ny
 * række, og 'card_align' bestemmer, om den sidste række midterstilles
 * eller starter fra venstre.
 *
 * Knappen følger samme mønster som navbarens links: en valgt side vinder
 * over en adresse skrevet i hånden, fordi den interne henvisning er den
 * robuste. Et kort uden knaptekst får ingen knap — en tom knap ville
 * bare være en klikflade uden formål.
 */
final class BoxCardsBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'boxcards';
    }

    public static function label(): string
    {
        return 'Box cards';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'        => 'text',
                'label'       => 'Overskrift',
                'placeholder' => 'Fx Begynderkursus efterår 2026',
                'default'     => 'Begynderkursus efterår 2026',
            ],
            'cards' => [
                'type'     => 'repeater',
                'label'    => 'Kort',
                'max_rows' => 12,
                'fields'   => [
                    'text' => [
                        'type'        => 'textarea',
                        'label'       => 'Tekst',
                        'placeholder' => 'Skriv en kort tekst',
                        'default'     => '',
                    ],
                    'button_label' => [
                        'type'        => 'text',
                        'label'       => 'Knaptekst (tom = ingen knap)',
                        'placeholder' => 'Fx Tryk her',
                        'default'     => '',
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Knappen går til siden',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Ekstern adresse',
                        'placeholder' => 'Indsæt link',
                        'default'     => '',
                    ],
                ],
                'default' => [
                    [
                        'text'         => 'Vi har oprettet en Facebook-gruppe, hvor alle medlemmer kan komme med input.',
                        'button_label' => 'Tryk her',
                        'page'         => 0,
                        'url'          => '',
                    ],
                    [
                        'text'         => 'Se hele programmet for sæsonen på vores hjemmeside.',
                        'button_label' => 'Tryk her',
                        'page'         => 0,
                        'url'          => '',
                    ],
                    [
                        'text'         => 'Gennem bridgen holdes hjernen i gang, og man stifter bekendtskab med nye mennesker.',
                        'button_label' => 'Tryk her',
                        'page'         => 0,
                        'url'          => '',
                    ],
                ],
            ],
            'card_align' => [
                'type'    => 'select',
                'label'   => 'Placering af kort (når de bryder ned i flere rækker)',
                'default' => 'center',

                // Simpel liste af værdier, som i footer og textarea.
                // FieldValidator sammenligner med værdierne selv.
                'options' => ['center', 'left'],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund bag kortene',
                'default' => '#757675',
            ],
            'title_bg' => [
                'type'    => 'color',
                'label'   => 'Overskriftens bjælke',
                'default' => '#757675',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskriftens tekstfarve',
                'default' => '#ffffff',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på overskrift',
                'default' => 28,
                'min'     => 12,
                'max'     => 72,
                'unit'    => 'px',
            ],
            'card_bg' => [
                'type'    => 'color',
                'label'   => 'Kortenes baggrund',
                'default' => '#f5f5f5',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve i kort',
                'default' => '#000000',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse i kort',
                'default' => 16,
                'min'     => 10,
                'max'     => 32,
                'unit'    => 'px',
            ],
            'button_bg' => [
                'type'    => 'color',
                'label'   => 'Knappens farve',
                'default' => '#95acbf',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knappens tekstfarve',
                'default' => '#5f4589',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],

            // Overskriftsbjælken, den brede flade bagved og hvert enkelt
            // kort kan ændres i størrelse. Afrundingen er 9 px i designet.
            // Bjælken har ingen højde: den følger teksten.
            ...static::boxStyleFields('title', 'Overskriftens bjælke', ['width', 'radius'], ['radius' => 9]),
            ...static::boxStyleFields('box', 'Baggrundsflade', ['width', 'height', 'radius'], ['radius' => 9]),
            ...static::boxStyleFields('card', 'Kort', ['width', 'height', 'radius'], ['radius' => 9]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $cards = [];

        foreach ((array) ($settings['cards'] ?? []) as $card) {
            if (!is_array($card)) {
                continue;
            }

            $text  = trim((string) ($card['text'] ?? ''));
            $label = trim((string) ($card['button_label'] ?? ''));

            // Et kort helt uden indhold ville bare være en tom flade.
            if ($text === '' && $label === '') {
                continue;
            }

            $pageId = (int) ($card['page'] ?? 0);

            // Adressen regnes ud her, ikke i templaten. Templaten skal
            // hverken kende sitets struktur eller sin egen placering.
            $href = $pageId > 0
                ? $context->pageUrl($pageId)
                : (string) ($card['url'] ?? '');

            $cards[] = [
                'text'  => $text,
                'label' => $label,
                'href'  => $href !== '' ? $href : '#',
            ];
        }

        $align = (string) ($settings['card_align'] ?? 'center');

        return static::renderTemplate([
            'title'   => (string) ($settings['title'] ?? ''),
            'cards'   => $cards,
            'align'   => $align === 'left' ? 'left' : 'center',
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}
