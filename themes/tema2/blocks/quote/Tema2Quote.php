<?php
declare(strict_types=1);

/**
 * Citat med billede til tema 2.
 *
 * Et stort lyst kort med billede til venstre og et citat, en kort tekst og
 * en knap til højre. Kortet står 60 % over og 40 % ned i en mørk flade,
 * der går kant til kant. En spar sidder i kortets nederste højre hjørne.
 *
 * Citattegnene sættes på automatisk. Skriver brugeren dem selv, fjernes de
 * først, så der aldrig står to sæt.
 */
final class Tema2QuoteBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-quote';
    }

    public static function label(): string
    {
        return 'Citat med billede — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'quote' => [
                'type'    => 'textarea',
                'label'   => 'Citat (citattegn sættes på automatisk)',
                'default' => 'Det bedste ved klubben er fællesskabet ved bordet',
                'max'     => 200,
            ],
            'text' => [
                'type'    => 'textarea',
                'label'   => 'Tekst',
                'default' => 'Fortæl her, hvad I tilbyder nye spillere — fx åbent hus, '
                    . 'undervisning før spillestart eller hjælp til at finde en makker.',
                'max'     => 500,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld dig nu',
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
                'default' => 'Glade medlemmer efter en turnering',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'top_color' => [
                'type'    => 'color',
                'label'   => 'Farven over den mørke flade',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Mørk flade',
                'default' => '#1d3444',
                'group'   => 'Farver',
            ],
            'card_color' => [
                'type'    => 'color',
                'label'   => 'Kortet',
                'default' => '#fafafa',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#111111',
                'group'   => 'Farver',
            ],
            'shadow_color' => [
                'type'    => 'color',
                'label'   => 'Kortets kant/skygge',
                'default' => '#817474',
                'group'   => 'Farver',
            ],
            'quote_size' => [
                'type'    => 'number',
                'label'   => 'Citatets størrelse',
                'default' => 28,
                'min'     => 18,
                'max'     => 56,
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
                'label'   => 'Afrunding (kort og billede)',
                'default' => 25,
                'min'     => 0,
                'max'     => 60,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'image_side' => [
                'type'    => 'select',
                'label'   => 'Billedet står til',
                'default' => 'Venstre',
                'options' => ['Venstre', 'Højre'],
                'group'   => 'Form',
            ],
            'suit' => [
                'type'    => 'select',
                'label'   => 'Spar i hjørnet',
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
        $image  = (string) ($settings['image'] ?? '');

        // Citattegn, brugeren selv har skrevet, fjernes — vi sætter dem på.
        $quote = trim((string) ($settings['quote'] ?? ''));
        $quote = trim($quote, " \t\n\r\"'“”„«»");

        return static::renderTemplate([
            'quote'       => $quote,
            // Ordene hver for sig, så de kan toner ind ét ad gangen.
            'words'       => $quote === '' ? [] : (preg_split('/\s+/u', $quote) ?: []),
            'text'        => (string) ($settings['text'] ?? ''),
            'buttonLabel' => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'  => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
            'image'       => $image !== '' ? $context->asset($image) : '',
            'imageAlt'    => (string) ($settings['image_alt'] ?? ''),
            'imageRight'  => ($styles['image_side'] ?? 'Venstre') === 'Højre',
            'showSuit'    => ($styles['suit'] ?? 'Vis') === 'Vis',
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }
}
