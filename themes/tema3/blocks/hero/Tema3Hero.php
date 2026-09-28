<?php
declare(strict_types=1);

/**
 * Hero til tema 3.
 *
 * Et stort billede, der kommer ind fra venstre skærmkant og har bløde
 * hjørner i højre side. Til højre for det:
 *   - overskriften øverst, i flugt med billedets overkant,
 *   - nederst en kort tekst og to knapper, der går helt ud til højre
 *     skærmkant. Den nederste knap flugter med billedets underkant.
 *
 * Knapperne udvider sig mod venstre ved hover, bliver røde, og en lille
 * cirkel "swoosher" ud under deres nederste venstre hjørne.
 */
final class Tema3HeroBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-hero';
    }

    public static function label(): string
    {
        return 'Hero — tema 3';
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
                'default' => 'Skriv et par linjer om klubben her – hvor I spiller, og hvilke dage I mødes. Nye spillere er altid velkomne.',
                'max'     => 400,
            ],
            'button1_label' => [
                'type'        => 'text',
                'label'       => 'Knap 1: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Bliv medlem',
                'max'         => 40,
            ],
            'button1_page' => [
                'type'    => 'page',
                'label'   => 'Knap 1: side',
                'default' => 0,
            ],
            'button1_url' => [
                'type'        => 'url',
                'label'       => 'Knap 1: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'button2_label' => [
                'type'        => 'text',
                'label'       => 'Knap 2: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Se program',
                'max'         => 40,
            ],
            'button2_page' => [
                'type'    => 'page',
                'label'   => 'Knap 2: side',
                'default' => 0,
            ],
            'button2_url' => [
                'type'        => 'url',
                'label'       => 'Knap 2: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede',
                'default' => 'themes/tema2/assets/heroimage.jpg',
            ],
            'image_alt' => [
                'type'        => 'text',
                'label'       => 'Billede: beskrivelse (alt-tekst)',
                'placeholder' => 'Hvad viser billedet?',
                'default'     => 'Et spillekort — klør es — på et træbord',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'title_start' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til venstre',
                'default' => '#3d3a3a',
                'group'   => 'Tekst',
            ],
            'title_end' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til højre',
                'default' => '#c83030',
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
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#d55c5c',
                'group'   => 'Tekst',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Tekst',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#bcb6b6',
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
            'radius' => [
                'type'    => 'number',
                'label'   => 'Afrunding (billede og knapper)',
                'default' => 40,
                'min'     => 0,
                'max'     => 80,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'image_width' => [
                'type'    => 'number',
                'label'   => 'Billedets bredde (del af skærmen)',
                'default' => 38,
                'min'     => 25,
                'max'     => 60,
                'unit'    => '%',
                'group'   => 'Form',
            ],
            'image_height' => [
                'type'    => 'number',
                'label'   => 'Billedets højde',
                'default' => 760,
                'min'     => 400,
                'max'     => 1100,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $buttons = [];

        foreach ([1, 2] as $n) {
            $label = trim((string) ($settings["button{$n}_label"] ?? ''));

            // I editoren står en tom knap der stadig, så man kan klikke
            // på den og skrive en tekst. På siden forsvinder den.
            if ($label === '' && !$context->isInlineEditing()) {
                continue;
            }

            $pageId = (int) ($settings["button{$n}_page"] ?? 0);
            $url    = (string) ($settings["button{$n}_url"] ?? '');

            $buttons[] = [
                'field' => "button{$n}_label",
                'label' => $label,
                // Side før adresse, ligesom i navbaren.
                'href'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        $image = (string) ($settings['image'] ?? '');

        return static::renderTemplate([
            'title'    => (string) ($settings['title'] ?? ''),
            'text'     => (string) ($settings['text'] ?? ''),
            'buttons'  => $buttons,
            'image'    => $image !== '' ? $context->asset($image) : '',
            'imageAlt' => (string) ($settings['image_alt'] ?? ''),
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
        ]);
    }
}
