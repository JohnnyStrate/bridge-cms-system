<?php
declare(strict_types=1);

/**
 * Tekstboks: en farvet boks med en lang tekst, et lille kort i øverste
 * højre hjørne og en afslutning med overskrift og knap.
 *
 * Boksen fylder ikke hele sidens bredde — den har en maksimal bredde og
 * står i midten. Bredden kan ændres i editoren.
 *
 * DET LILLE KORT FLYDER
 * Kortet er sat til at flyde til højre i CSS'en. Derfor lægger teksten
 * sig selv omkring det: smal øverst, hvor kortet fylder, og i fuld
 * bredde igen, når teksten når forbi kortet. Alternativet — to faste
 * spalter — ville efterlade et tomt hul under kortet, når teksten er
 * lang, og det er netop dét, designet undgår.
 *
 * Begge knapper følger samme mønster som navbarens links: en valgt side
 * vinder over en adresse skrevet i hånden, fordi den interne henvisning
 * overlever, at målsiden får en ny slug. Er knapteksten tom, tegnes
 * knappen slet ikke — en knap uden tekst er bare en klikflade.
 */
final class TextBoxBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'textbox';
    }

    public static function label(): string
    {
        return 'Tekstboks';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'        => 'text',
                'label'       => 'Overskrift',
                'placeholder' => 'Fx 10. juli 2026',
                'default'     => '10. juli 2026',
            ],
            'body' => [
                'type'        => 'textarea',
                'label'       => 'Tekst',
                'placeholder' => 'Skriv nyheden eller referatet her',

                // Lang tekst er hele pointen med blokken, så loftet er
                // sat højere end standardens 5000.
                'max'         => 20000,
                'default'     => "Vores afholdte spiller er desværre gået bort i alt for tidlig alder.\nHan var altid venlig, imødekommende og hjælpsom.\nÆret være hans minde.",
            ],

            /*
             * Kortet og afslutningen får hver sin 'group'. Så samler
             * editoren dem i en ramme med gruppens navn som overskrift,
             * og etiketterne slipper for at gentage, hvor de hører til.
             */

            // --- Det lille kort i hjørnet ---
            'card_text' => [
                'type'        => 'textarea',
                'group'       => 'Kortet i hjørnet',
                'label'       => 'Tekst (tom = intet kort)',
                'placeholder' => 'En kort tekst i hjørnet',
                'default'     => 'Gennem bridgen holdes hjernen i gang, og man bliver ved med at stifte bekendtskab med nye mennesker.',
            ],
            'card_button_label' => [
                'type'        => 'text',
                'group'       => 'Kortet i hjørnet',
                'label'       => 'Knaptekst (tom = ingen knap)',
                'placeholder' => 'Fx Tryk her',
                'default'     => 'Tryk her',
            ],
            'card_page' => [
                'type'    => 'page',
                'group'   => 'Kortet i hjørnet',
                'label'   => 'Knappen går til siden',
                'default' => 0,
            ],
            'card_url' => [
                'type'        => 'url',
                'group'       => 'Kortet i hjørnet',
                'label'       => 'Ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],

            // --- Afslutningen nederst ---
            'footer_title' => [
                'type'        => 'text',
                'group'       => 'Nederst i boksen',
                'label'       => 'Overskrift (tom = ingen afslutning)',
                'placeholder' => 'Fx Tilmelding på hjemmesiden',
                'default'     => 'Tilmelding på hjemmesiden',
            ],
            'footer_button_label' => [
                'type'        => 'text',
                'group'       => 'Nederst i boksen',
                'label'       => 'Knaptekst (tom = ingen knap)',
                'placeholder' => 'Fx Tryk her',
                'default'     => 'Tryk her',
            ],
            'footer_page' => [
                'type'    => 'page',
                'group'   => 'Nederst i boksen',
                'label'   => 'Knappen går til siden',
                'default' => 0,
            ],
            'footer_url' => [
                'type'        => 'url',
                'group'       => 'Nederst i boksen',
                'label'       => 'Ekstern adresse',
                'placeholder' => 'Indsæt link',
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
                'default' => '#696969',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve i boksen',
                'default' => '#ffffff',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på overskrift',
                'default' => 20,
                'min'     => 12,
                'max'     => 64,
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
            'card_bg' => [
                'type'    => 'color',
                'label'   => 'Kortets baggrund',
                'default' => '#f5f5f5',
            ],
            'card_text_color' => [
                'type'    => 'color',
                'label'   => 'Kortets tekstfarve',
                'default' => '#000000',
            ],
            'button_bg' => [
                'type'    => 'color',
                'label'   => 'Knappernes farve',
                'default' => '#95acbf',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knappernes tekstfarve',
                'default' => '#5f4589',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],

            // Den store boks fylder ikke hele siden. Uden et valgt tal
            // bruger CSS'en sin egen maksimale bredde.
            ...static::boxStyleFields('box', 'Den store boks', ['width', 'height', 'radius'], ['radius' => 9]),
            ...static::boxStyleFields('card', 'Kortet i hjørnet', ['width', 'height', 'radius'], ['radius' => 9]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        /**
         * Adressen til en knap. Regnes ud her og ikke i templaten, så
         * templaten hverken kender sitets struktur eller sin placering.
         */
        $href = static function (int $pageId, string $url) use ($context): string {
            $address = $pageId > 0 ? $context->pageUrl($pageId) : $url;

            return $address !== '' ? $address : '#';
        };

        return static::renderTemplate([
            'title' => (string) ($settings['title'] ?? ''),
            'body'  => trim((string) ($settings['body'] ?? '')),

            'cardText'   => trim((string) ($settings['card_text'] ?? '')),
            'cardLabel'  => trim((string) ($settings['card_button_label'] ?? '')),
            'cardHref'   => $href(
                (int) ($settings['card_page'] ?? 0),
                (string) ($settings['card_url'] ?? '')
            ),

            'footerTitle' => trim((string) ($settings['footer_title'] ?? '')),
            'footerLabel' => trim((string) ($settings['footer_button_label'] ?? '')),
            'footerHref'  => $href(
                (int) ($settings['footer_page'] ?? 0),
                (string) ($settings['footer_url'] ?? '')
            ),

            'cssVars' => static::cssVariables($styles),
        ]);
    }
}
