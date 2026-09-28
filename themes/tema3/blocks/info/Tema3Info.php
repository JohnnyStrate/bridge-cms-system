<?php
declare(strict_types=1);

/**
 * Info med billede til tema 3 — fx "ÅBEN ÅRET RUNDT".
 *
 * Øverst en titellinje mellem to bløde pile:  >  TITEL  MINITITEL  <
 * Under den en rød gradient-boks med tekst til venstre og et billede med
 * rødt overlay til højre. Nederst på billedet ligger op til to knapper.
 *
 * FORMATERING I TEKSTEN (samme system som tema 2's "Tekst og billede")
 *     *fremhævet*  → fed kursiv i mørk farve — fx "*MobilePay 00000.*"
 *     **fed**      → ekstra fed
 *     tom linje    → nyt afsnit
 * Teksten escapes FØR formateringen lægges på, så der aldrig kommer rå
 * HTML fra brugeren ud på siden.
 */
final class Tema3InfoBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-info';
    }

    public static function label(): string
    {
        return 'Info med billede — tema 3';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Åben året rundt',
                'max'     => 60,
            ],
            'eyebrow' => [
                'type'    => 'text',
                'label'   => 'Minititel (til højre for overskriften)',
                'default' => 'Fællesskab',
                'max'     => 30,
            ],
            'body' => [
                'type'    => 'textarea',
                'label'   => 'Tekst — *fremhævet* (fx MobilePay), **ekstra fed**, tom linje = nyt afsnit',
                'default' => "Skriv her, hvornår og hvor I spiller — fx hver mandag kl. 18.45 "
                    . "året rundt. Fortæl også, hvad det koster at være med, og om nogen "
                    . "spiller til nedsat pris.\n\n"
                    . "Betal ved tilmelding på *MobilePay 00000.* Sidste frist for "
                    . "tilmelding er kl. 12 på spilledagen.",
                'max'     => 3000,
            ],
            'note' => [
                'type'    => 'textarea',
                'label'   => 'NB-linje (tom = ingen)',
                'default' => 'NB! Skriv en vigtig bemærkning her, fx om tilmelding eller makkere.',
                'max'     => 400,
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
            'button1_label' => [
                'type'        => 'text',
                'label'       => 'Knap 1: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld dig',
                'max'         => 30,
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
                'default'     => 'Læs mere',
                'max'         => 30,
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
                'default' => '#bf6356',
                'group'   => 'Overskrift',
            ],
            'title_end' => [
                'type'    => 'color',
                'label'   => 'Overskrift: farve til højre',
                'default' => '#9f9a99',
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
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Boks',
            ],
            'highlight_color' => [
                'type'    => 'color',
                'label'   => 'Fremhævet tekst (*…*)',
                'default' => '#2f2f2f',
                'group'   => 'Boks',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 25,
                'min'     => 12,
                'max'     => 32,
                'unit'    => 'px',
                'group'   => 'Boks',
            ],
            'overlay_start' => [
                'type'    => 'color',
                'label'   => 'Overlay: farve øverst',
                'default' => '#ad4141',
                'group'   => 'Billede',
            ],
            'overlay_end' => [
                'type'    => 'color',
                'label'   => 'Overlay: farve nederst',
                'default' => '#ffffff',
                'group'   => 'Billede',
            ],
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Overlayets styrke',
                'default' => 39,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Billede: afrunding',
                'default' => 18,
                'min'     => 0,
                'max'     => 60,
                'unit'    => 'px',
                'group'   => 'Billede',
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
        $buttons = [];

        foreach ([1, 2] as $n) {
            $label = trim((string) ($settings["button{$n}_label"] ?? ''));

            // I editoren står en tom knap der stadig, så man kan klikke på
            // den og skrive en tekst. På siden forsvinder den.
            if ($label === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($settings["button{$n}_page"] ?? 0);
            $url    = (string) ($settings["button{$n}_url"] ?? '');

            $buttons[] = [
                'field' => "button{$n}_label",
                'label' => $label,
                'href'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            ];
        }

        $image = (string) ($settings['image'] ?? '');

        return static::renderTemplate([
            'title'      => (string) ($settings['title'] ?? ''),
            'eyebrow'    => trim((string) ($settings['eyebrow'] ?? '')),
            'paragraphs' => self::formatText((string) ($settings['body'] ?? '')),
            'note'       => self::formatInline(trim((string) ($settings['note'] ?? ''))),
            'bodyRaw'    => (string) ($settings['body'] ?? ''),
            'noteRaw'    => (string) ($settings['note'] ?? ''),
            'buttons'    => $buttons,
            'image'      => $image !== '' ? $context->asset($image) : '',
            'imageAlt'   => (string) ($settings['image_alt'] ?? ''),
            'cssVars'    => static::cssVariables($styles),
            'context'    => $context,
        ]);
    }

    /**
     * Deler teksten i afsnit ved tomme linjer og formaterer hvert afsnit.
     *
     * @return array<int, string> Færdig, sikker HTML pr. afsnit.
     */
    private static function formatText(string $text): array
    {
        $text   = str_replace(["\r\n", "\r"], "\n", trim($text));
        $blocks = preg_split('/\n\s*\n/', $text) ?: [];
        $result = [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block !== '') {
                $result[] = nl2br(self::formatInline($block), false);
            }
        }

        return $result;
    }

    /**
     * **fed** og *fremhævet* på én tekst. Først escapes ALT, så indsættes
     * vores egne tags — det eneste, der kan blive til HTML, er <strong> og
     * <em>, som vi selv skriver.
     */
    private static function formatInline(string $text): string
    {
        $html = e($text);
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html) ?? $html;

        return $html;
    }
}
