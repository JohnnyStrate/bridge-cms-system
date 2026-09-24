<?php
declare(strict_types=1);

/**
 * Footer til tema 1.
 *
 * Makker til tema 1's navbar: samme cremefarve og accent, og en skrå
 * OVERkant, der spejler navbarens skrå underkant. Links er piller med samme
 * hover-animation som i navbaren.
 *
 * Den er tema 1's globale footer: Tema1Theme::globals() peger på
 * 'tema1-footer' i slot'en 'footer'.
 *
 * E-mail og telefon får deres href bygget her, ikke i templaten, så et felt
 * med 'javascript:...' aldrig kan ende som et klikbart link.
 */
final class Tema1FooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema1-footer';
    }

    public static function label(): string
    {
        return 'Footer — tema 1';
    }

    public static function getSchema(): array
    {
        return [
            'club_name' => [
                'type'    => 'text',
                'label'   => 'Klubbens navn',
                'default' => 'Bridgeklubben',
            ],
            'tagline' => [
                'type'    => 'text',
                'label'   => 'Kort tekst under navnet',
                'default' => 'Spil, hygge og turneringer hele året.',
            ],
            'address' => [
                'type'    => 'text',
                'label'   => 'Adresse',
                'default' => 'Vejnavn 1, 1234 By',
            ],
            'email' => [
                'type'    => 'text',
                'label'   => 'E-mail',
                'default' => 'info@klub.dk',
                'max'     => 254,
            ],
            'phone' => [
                'type'    => 'text',
                'label'   => 'Telefon',
                'default' => '+45 00 00 00 00',
                'max'     => 40,
            ],
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
                    ['label' => 'Forside', 'page' => 0, 'url' => '#'],
                    ['label' => 'Turneringer', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'copyright' => [
                'type'    => 'text',
                'label'   => 'Bundtekst (skriv {år} for årstallet)',
                'default' => '© {år} Bridgeklubben',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#f6f1e8',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#241f1c',
                'group'   => 'Farver',
            ],
            'accent_color' => [
                'type'    => 'color',
                'label'   => 'Accent',
                'default' => '#e2574c',
                'group'   => 'Farver',
            ],
            'accent_text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst på accent',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 15,
                'min'     => 10,
                'max'     => 32,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            // Hvor meget den skrå overkant skærer. 0 = lige kant.
            'slant' => [
                'type'    => 'number',
                'label'   => 'Skrå overkant',
                'default' => 28,
                'min'     => 0,
                'max'     => 80,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Form',
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

            if ($text === '') {
                continue;
            }

            $pageId = (int) ($link['page'] ?? 0);
            $href   = $pageId > 0
                ? $context->pageUrl($pageId)
                : (string) ($link['url'] ?? '');

            $links[] = [
                'label' => $text,
                'href'  => $href !== '' ? $href : '#',
            ];
        }

        $email = trim((string) ($settings['email'] ?? ''));
        $phone = trim((string) ($settings['phone'] ?? ''));
        $digits = preg_replace('/[^0-9+]/', '', $phone) ?? '';

        $clubName = trim((string) ($settings['club_name'] ?? ''));

        return static::renderTemplate([
            'clubName'  => $clubName,
            'mark'      => $clubName !== '' ? mb_substr($clubName, 0, 1) : '',
            'tagline'   => (string) ($settings['tagline'] ?? ''),
            'address'   => (string) ($settings['address'] ?? ''),
            'email'     => $email,
            'emailHref' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? 'mailto:' . $email : '',
            'phone'     => $phone,
            'phoneHref' => $digits !== '' ? 'tel:' . $digits : '',
            'links'     => $links,
            'copyright' => str_replace('{år}', date('Y'), (string) ($settings['copyright'] ?? '')),
            'cssVars'   => static::cssVariables($styles),
            'context'   => $context,
        ]);
    }
}
