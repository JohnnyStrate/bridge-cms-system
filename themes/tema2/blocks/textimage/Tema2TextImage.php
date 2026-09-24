<?php
declare(strict_types=1);

/**
 * Tekst og billede til tema 2.
 *
 * Venstre: lille overlinje ("FÆLLESSKAB"), stor overskrift, brødtekst, en
 * valgfri NB-linje og en knap. Højre: et billede i en blød, "wobbly" cirkel
 * med skygge og små kortikoner. Siderne kan byttes om i editoren.
 *
 * FORMATERING I TEKSTEN
 * Brugeren skriver almindelig tekst med tre små regler:
 *     **fed**      → halvfed (Jost Semibold)
 *     *kursiv*     → kursiv
 *     tom linje    → nyt afsnit
 * Teksten escapes FØR formateringen lægges på, så der aldrig kommer rå HTML
 * fra brugeren ud på siden. Se formatText().
 *
 * I editoren vises teksten rå — med stjernerne synlige — så den kan
 * redigeres direkte på siden uden at formateringen går tabt.
 */
final class Tema2TextImageBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-textimage';
    }

    public static function label(): string
    {
        return 'Tekst og billede — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'eyebrow' => [
                'type'    => 'text',
                'label'   => 'Lille overlinje',
                'default' => 'Fællesskab',
                'max'     => 40,
            ],
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Åben året rundt',
            ],
            'body' => [
                'type'    => 'textarea',
                'label'   => 'Tekst — **fed**, *kursiv*, tom linje = nyt afsnit',
                'default' => "Fortæl her, hvornår og hvor I spiller — fx hvilken ugedag, "
                    . "klokkeslæt og adresse. Skriv også, hvad det koster at være med.\n\n"
                    . "Har I en fast måde at tilmelde sig på, kan den fremhæves: "
                    . "**Betal med MobilePay 00000.** Sidste frist for tilmelding er kl. 12 "
                    . "på spilledagen.",
                'max'     => 3000,
            ],
            'note' => [
                'type'    => 'textarea',
                'label'   => 'NB-linje (tom = ingen)',
                'default' => 'NB! Skriv en vigtig bemærkning her, fx om tilmelding eller makkere.',
                'max'     => 400,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Se mere',
                'max'         => 40,
            ],
            'button_page' => [
                'type'    => 'page',
                'label'   => 'Knap: side',
                'default' => 0,
            ],
            'button_url' => [
                'type'        => 'url',
                'label'       => 'Knap: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede',
                'default' => 'themes/tema2/assets/welcomeimage.png',
            ],
            'image_alt' => [
                'type'    => 'text',
                'label'   => 'Beskrivelse af billedet',
                'default' => 'Medlemmer ved et bord',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'eyebrow_color' => [
                'type'    => 'color',
                'label'   => 'Overlinje',
                'default' => '#c0c0c0',
                'group'   => 'Farver',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift',
                'default' => '#343d39',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#4b4b4b',
                'group'   => 'Farver',
            ],
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 72,
                'min'     => 28,
                'max'     => 120,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 16,
                'min'     => 12,
                'max'     => 24,
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
            'image_side' => [
                'type'    => 'select',
                'label'   => 'Billedet står til',
                'default' => 'Højre',
                'options' => ['Højre', 'Venstre'],
                'group'   => 'Billede',
            ],
            'shadow_opacity' => [
                'type'    => 'number',
                'label'   => 'Skyggens styrke',
                'default' => 48,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'suits' => [
                'type'    => 'select',
                'label'   => 'Små kortikoner',
                'default' => 'Vis',
                'options' => ['Vis', 'Skjul'],
                'group'   => 'Billede',
            ],
            'button_start' => [
                'type'    => 'color',
                'label'   => 'Knap: farve til venstre',
                'default' => '#192e3c',
                'group'   => 'Knap',
            ],
            'button_end' => [
                'type'    => 'color',
                'label'   => 'Knap: farve til højre',
                'default' => '#457ba2',
                'group'   => 'Knap',
            ],
            'button_opacity' => [
                'type'    => 'number',
                'label'   => 'Knap: dækkeevne',
                'default' => 72,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Knap',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Knap: afrunding',
                'default' => 12,
                'min'     => 0,
                'max'     => 40,
                'unit'    => 'px',
                'group'   => 'Knap',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $label  = trim((string) ($settings['button_label'] ?? ''));
        $pageId = (int) ($settings['button_page'] ?? 0);
        $url    = (string) ($settings['button_url'] ?? '');
        $image  = (string) ($settings['image'] ?? '');

        return static::renderTemplate([
            'eyebrow'     => trim((string) ($settings['eyebrow'] ?? '')),
            'title'       => (string) ($settings['title'] ?? ''),
            'paragraphs'  => self::formatText((string) ($settings['body'] ?? '')),
            'note'        => self::formatInline(trim((string) ($settings['note'] ?? ''))),
            'bodyRaw'     => (string) ($settings['body'] ?? ''),
            'noteRaw'     => (string) ($settings['note'] ?? ''),
            'buttonLabel' => $label,
            'buttonHref'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            'image'       => $image !== '' ? $context->asset($image) : '',
            'imageAlt'    => (string) ($settings['image_alt'] ?? ''),
            'imageLeft'   => ($styles['image_side'] ?? 'Højre') === 'Venstre',
            'showSuits'   => ($styles['suits'] ?? 'Vis') === 'Vis',
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
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
     * **fed** og *kursiv* på én tekst.
     *
     * Rækkefølgen er vigtig: først escapes ALT, så indsættes vores egne
     * tags. Stjernerne overlever escaping uændret, og det eneste, der kan
     * blive til HTML, er <strong> og <em>, som vi selv skriver.
     */
    private static function formatInline(string $text): string
    {
        $html = e($text);
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html) ?? $html;

        return $html;
    }
}
