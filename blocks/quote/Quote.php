<?php
declare(strict_types=1);

/**
 * Quote: et billede til venstre og en farvet boks med et citat til højre.
 *
 * Billedet er valgfrit. Fjernes det, fylder boksen hele bredden, så der
 * ikke står et tomt hul tilbage.
 *
 * Boksen har tre tekster med hver sin rolle: selve citatet, en kort
 * uddybning under det, og en lille kontaktoplysning nederst. De er tre
 * felter og ikke ét, fordi de skal have hver deres størrelse og vægt.
 *
 * Billedet lægger sig en smule ind over boksen. Overlappet slås fra på
 * små skærme, hvor de to dele står under hinanden i stedet.
 */
final class QuoteBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'quote';
    }

    public static function label(): string
    {
        return 'Quote-boks';
    }

    public static function getSchema(): array
    {
        return [
            'image' => [
                'type'    => 'image',
                'label'   => 'Billede (kan fjernes)',
                'default' => 'assets/demo/hero-placeholder.jpg',
            ],
            'image_alt' => [
                'type'        => 'text',
                'label'       => 'Beskrivelse af billedet',
                'placeholder' => 'Fx Vindere af sølvpointturneringen',
                'default'     => '',
            ],
            'quote' => [
                'type'        => 'textarea',
                'label'       => 'Citat',
                'placeholder' => 'Fx "det er sjovt, socialt og godt for hovedet"',
                'default'     => '"det er sjovt, socialt og godt for hovedet"',
            ],
            'body' => [
                'type'        => 'textarea',
                'label'       => 'Tekst under citatet',
                'placeholder' => 'En kort uddybning',
                'default'     => 'Gennem bridgen holdes hjernen i gang, og man bliver ved med at stifte bekendtskab med nye mennesker. Bridge er både sjovt, spændende og socialt. Bridge er for alle!',
            ],
            'note' => [
                'type'        => 'textarea',
                'label'       => 'Lille tekst nederst (fx kontakt)',
                'placeholder' => 'Fx navn, telefon og mail',
                'default'     => '',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Boksens farve',
                'default' => '#213377',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve',
                'default' => '#ffffff',
            ],
            'quote_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på citat',
                'default' => 26,
                'min'     => 12,
                'max'     => 72,
                'unit'    => 'px',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på tekst',
                'default' => 16,
                'min'     => 10,
                'max'     => 32,
                'unit'    => 'px',
            ],
            'note_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på lille tekst',
                'default' => 11,
                'min'     => 8,
                'max'     => 20,
                'unit'    => 'px',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],
            'overlap' => [
                'type'    => 'number',
                'label'   => 'Billedets overlap ind over boksen (0 = side om side)',
                'default' => 40,
                'min'     => 0,
                'max'     => 200,
                'unit'    => 'px',
            ],

            // Billedet og boksen kan sættes i størrelse hver for sig.
            // Afrundingen er 9 px på begge i designet.
            ...static::boxStyleFields('image', 'Billedet', ['width', 'height', 'radius'], ['radius' => 9]),
            ...static::boxStyleFields('box', 'Boksen', ['width', 'height', 'radius'], ['radius' => 9]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $image = (string) ($settings['image'] ?? '');

        return static::renderTemplate([
            // asset() oversætter den gemte sti til en adresse, der virker
            // både i editoren og i den eksporterede mappe.
            'image'    => $image !== '' ? $context->asset($image) : '',
            'imageAlt' => (string) ($settings['image_alt'] ?? ''),
            'quote'    => trim((string) ($settings['quote'] ?? '')),
            'body'     => trim((string) ($settings['body'] ?? '')),
            'note'     => trim((string) ($settings['note'] ?? '')),
            'cssVars'  => static::cssVariables($styles),
        ]);
    }
}
