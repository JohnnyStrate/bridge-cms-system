<?php
declare(strict_types=1);

/**
 * Mørk sektion med svævende overskrift til tema 2 ("Kom og spil bridge").
 *
 * Overskriften står i en afrundet boks, der hænger halvt over den mørke
 * flades øverste kant. Under den: tekst med samme formatering som i
 * "Tekst og billede" (**fed**, *kursiv*, tom linje = nyt afsnit), et par
 * kortikoner og en knap, hvis bredde følger teksten.
 *
 * Halvdelen over kanten er ren CSS: se "Boksen halvt over kanten" i
 * block.css. Den tilpasser sig selv, hvis overskriften fylder to linjer.
 */
final class Tema2CalloutBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-callout';
    }

    public static function label(): string
    {
        return 'Mørk sektion med overskrift — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Kom og spil bridge',
                'max'     => 80,
            ],
            'body' => [
                'type'    => 'textarea',
                'label'   => 'Tekst — **fed**, *kursiv*, tom linje = nyt afsnit',
                'default' => "Nysgerrig på bridge? Fortæl her om jeres begynderkursus — hvornår det "
                    . "starter, hvor mange gange I mødes, og hvad det koster.\n\n"
                    . "Man behøver ikke at have en makker med: **vi finder en til dig.** "
                    . "Kaffe og kage er *altid* med i prisen.\n\n"
                    . "Skriv til sidst, hvordan man melder sig til, og hvornår fristen er.\n\n"
                    . "NB! Skriv en vigtig bemærkning her, fx om hvor mange pladser der er.",
                'max'     => 3000,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst (maks. 50 tegn)',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld dig begynderkurset her',
                'max'         => 50,
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
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'top_color' => [
                'type'    => 'color',
                'label'   => 'Farven over den mørke flade',
                'default' => '#fafafa',
                'group'   => 'Farver',
            ],
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Mørk flade',
                'default' => '#16242f',
                'group'   => 'Farver',
            ],
            'title_start' => [
                'type'    => 'color',
                'label'   => 'Overskriftsboks: farve til venstre',
                'default' => '#152b3a',
                'group'   => 'Farver',
            ],
            'title_end' => [
                'type'    => 'color',
                'label'   => 'Overskriftsboks: farve til højre',
                'default' => '#153348',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 60,
                'min'     => 28,
                'max'     => 110,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 19,
                'min'     => 12,
                'max'     => 28,
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
            'radius' => [
                'type'    => 'number',
                'label'   => 'Overskriftsboks: afrunding',
                'default' => 22,
                'min'     => 0,
                'max'     => 60,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'suits' => [
                'type'    => 'select',
                'label'   => 'Små kortikoner',
                'default' => 'Vis',
                'options' => ['Vis', 'Skjul'],
                'group'   => 'Form',
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
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $pageId = (int) ($settings['button_page'] ?? 0);
        $url    = (string) ($settings['button_url'] ?? '');

        return static::renderTemplate([
            'title'       => (string) ($settings['title'] ?? ''),
            'paragraphs'  => self::formatText((string) ($settings['body'] ?? '')),
            'bodyRaw'     => (string) ($settings['body'] ?? ''),
            'buttonLabel' => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            'showSuits'   => ($styles['suits'] ?? 'Vis') === 'Vis',
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }

    /**
     * Samme regler som i Tema2TextImageBlock: escape først, så **fed**,
     * *kursiv* og tomme linjer som afsnit.
     *
     * @return array<int, string> Færdig, sikker HTML pr. afsnit.
     */
    private static function formatText(string $text): array
    {
        $text   = str_replace(["\r\n", "\r"], "\n", trim($text));
        $result = [];

        foreach (preg_split('/\n\s*\n/', $text) ?: [] as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            $html = e($block);
            $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html) ?? $html;
            $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html) ?? $html;

            $result[] = nl2br($html, false);
        }

        return $result;
    }
}
