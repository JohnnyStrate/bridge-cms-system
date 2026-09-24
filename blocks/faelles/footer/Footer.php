<?php
declare(strict_types=1);

/**
 * Footer: kontaktoplysninger og links nederst på siden.
 *
 * Blokken er bygget til at ligge i en global slot ('footer'), men der er
 * intet i klassen, der ved det. En blok kender hverken sin placering eller
 * om den er global — den beskriver kun sine felter og tegner sig selv.
 * Det er GlobalBlocks, der bestemmer resten.
 *
 * E-mail og telefon får deres href bygget HER og ikke i templaten.
 * Brugeren skriver 'info@klub.dk', ikke 'mailto:info@klub.dk' — og
 * adressen valideres, før den bliver til et link, så et felt med
 * 'javascript:...' aldrig kan ende som klikbart.
 */
final class FooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'footer';
    }

    public static function label(): string
    {
        return 'Footer';
    }

    public static function getSchema(): array
    {
        return [
            'club_name' => [
                'type'    => 'text',
                'label'   => 'Klubbens navn',
                'default' => 'Din klubs navn',
            ],
            'address' => [
                'type'    => 'text',
                'label'   => 'Adresse',
                'default' => 'Vejnavn 1, 1234 By',
            ],
            'email' => [
                'type'    => 'text',
                'label'   => 'E-mail',
                'default' => '',
                'max'     => 254,
            ],
            'phone' => [
                'type'    => 'text',
                'label'   => 'Telefon',
                'default' => '',
                'max'     => 40,
            ],
            // Samme mønster som navbarens links: en side vælges ved id,
            // så linket overlever, at målsiden får en ny slug.
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Links',
                'max_rows' => 20,
                'fields'   => [
                    'label' => [
                        'type'    => 'text',
                        'label'   => 'Tekst',
                        'default' => '',
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'    => 'url',
                        'label'   => 'Ekstern adresse',
                        'default' => '',
                    ],
                ],
                'default' => [
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'copyright' => [
                'type'    => 'text',
                'label'   => 'Bundtekst (skriv {år} for årstallet)',
                'default' => '© {år} Din klub',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrundsfarve',
                'default' => '#1e3a8a',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve',
                'default' => '#ffffff',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Tekstens størrelse',
                'default' => 15,
                'min'     => 10,
                'max'     => 32,
                'unit'    => 'px',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],
            'text_align' => [
                'type'    => 'select',
                'label'   => 'Justering',
                'default' => 'left',
                'options' => ['left', 'center'],
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $links = [];

        foreach ((array) ($settings['links'] ?? []) as $link) {
            if (!is_array($link)) {
                continue;
            }

            $text = trim((string) ($link['label'] ?? ''));

            // Et link uden tekst ville være en usynlig klikflade.
            if ($text === '') {
                continue;
            }

            $pageId = (int) ($link['page'] ?? 0);

            // Intern henvisning vinder over den eksterne adresse — den er
            // den robuste af de to.
            $href = $pageId > 0
                ? $context->pageUrl($pageId)
                : (string) ($link['url'] ?? '#');

            $links[] = [
                'label' => $text,
                'href'  => $href !== '' ? $href : '#',
            ];
        }

        $email = trim((string) ($settings['email'] ?? ''));
        $phone = trim((string) ($settings['phone'] ?? ''));

        // Kun en adresse, PHP selv anerkender som e-mail, bliver til et
        // mailto-link. Alt andet vises som ren tekst.
        $emailHref = filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            ? 'mailto:' . $email
            : '';

        // Visningen beholder brugerens formatering ('+45 20 00 00 00'),
        // mens href kun får cifre og plus — det er, hvad tel: forventer.
        $digits    = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        $phoneHref = $digits !== '' ? 'tel:' . $digits : '';

        // {år} erstattes ved rendering. Ved eksport fryses årstallet i
        // HTML-filen — det opdateres altså, næste gang sitet bygges.
        $copyright = str_replace(
            '{år}',
            date('Y'),
            (string) ($settings['copyright'] ?? '')
        );

        return static::renderTemplate([
            'clubName'  => (string) ($settings['club_name'] ?? ''),
            'address'   => (string) ($settings['address'] ?? ''),
            'email'     => $email,
            'emailHref' => $emailHref,
            'phone'     => $phone,
            'phoneHref' => $phoneHref,
            'links'     => $links,
            'copyright' => $copyright,
            'cssVars'   => static::cssVariables($styles),
            'context' => $context,
        ]);
    }
}