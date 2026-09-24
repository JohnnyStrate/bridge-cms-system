<?php
declare(strict_types=1);

/**
 * Footer til tema 2.
 *
 * Samme blå gradient som navbarens pille, med en bølget overkant — hero'ens
 * bølge vendt på hovedet. Bølgen fyldes med farven på sektionen ovenover,
 * så footeren "flyder" op i den.
 *
 * Indhold: logo, en kort tekst og en knap; kolonner med kontakt,
 * spilletider og genveje; og en bundlinje med © og "til toppen".
 *
 * Den er tema 2's globale footer: Tema2Theme::globals() peger på
 * 'tema2-footer' i slot'en 'footer'.
 *
 * E-mail og telefon får deres href bygget her, så et felt med
 * 'javascript:...' aldrig kan ende som et klikbart link.
 */
final class Tema2FooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-footer';
    }

    public static function label(): string
    {
        return 'Footer — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'logo' => [
                'type'    => 'image',
                'label'   => 'Logo',
                'default' => 'themes/tema2/assets/logo-placeholder.svg',
            ],
            'logo_alt' => [
                'type'    => 'text',
                'label'   => 'Beskrivelse af logo',
                'default' => 'Klubbens logo',
            ],
            'tagline' => [
                'type'    => 'textarea',
                'label'   => 'Kort tekst om klubben',
                'default' => 'Skriv et par linjer om klubben her — hvem I er, og at alle er velkomne ved bordet.',
                'max'     => 300,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Bliv medlem',
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
            'address' => [
                'type'    => 'textarea',
                'label'   => 'Adresse',
                'default' => "Klubhuset\nVejnavn 1, 1234 By",
                'max'     => 200,
            ],
            'phone' => [
                'type'    => 'text',
                'label'   => 'Telefon',
                'default' => '+45 00 00 00 00',
                'max'     => 40,
            ],
            'email' => [
                'type'    => 'text',
                'label'   => 'E-mail',
                'default' => 'info@dinklub.dk',
                'max'     => 254,
            ],
            'hours' => [
                'type'     => 'repeater',
                'label'    => 'Spilletider (dag, tekst)',
                'max_rows' => 8,
                'fields'   => [
                    'day' => [
                        'type'    => 'text',
                        'label'   => 'Dag',
                        'default' => '',
                        'max'     => 30,
                    ],
                    'text' => [
                        'type'    => 'text',
                        'label'   => 'Tid og hvad',
                        'default' => '',
                        'max'     => 60,
                    ],
                ],
                'default' => [
                    ['day' => 'Mandag', 'text' => 'kl. 18.45 — klubaften'],
                    ['day' => 'Onsdag', 'text' => 'kl. 13.00 — eftermiddagsbridge'],
                    ['day' => 'Juli', 'text' => 'sommerpause'],
                ],
            ],
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Genveje',
                'max_rows' => 12,
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
                    ['label' => 'Om klubben', 'page' => 0, 'url' => '#'],
                    ['label' => 'Turneringer', 'page' => 0, 'url' => '#'],
                    ['label' => 'Resultater', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'copyright' => [
                'type'    => 'text',
                'label'   => 'Bundtekst (skriv {år} for årstallet)',
                'default' => '© {år} Din Bridgeklub',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'gradient_start' => [
                'type'    => 'color',
                'label'   => 'Baggrund: farve til venstre',
                'default' => '#152b3a',
                'group'   => 'Farver',
            ],
            'gradient_end' => [
                'type'    => 'color',
                'label'   => 'Baggrund: farve til højre',
                'default' => '#2c5677',
                'group'   => 'Farver',
            ],
            'wave_color' => [
                'type'    => 'color',
                'label'   => 'Bølgen (farven på sektionen over)',
                'default' => '#1d3444',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'accent_color' => [
                'type'    => 'color',
                'label'   => 'Overskrifter i kolonnerne',
                'default' => '#f0a21e',
                'group'   => 'Farver',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Form',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Afrunding (logo og knap)',
                'default' => 12,
                'min'     => 0,
                'max'     => 40,
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
        $links = [];

        foreach ((array) ($settings['links'] ?? []) as $link) {
            if (!is_array($link) || trim((string) ($link['label'] ?? '')) === '') {
                continue;
            }

            $links[] = [
                'label' => trim((string) $link['label']),
                'href'  => self::hrefFor((int) ($link['page'] ?? 0), (string) ($link['url'] ?? ''), $context),
            ];
        }

        $hours = [];

        foreach ((array) ($settings['hours'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $day  = trim((string) ($row['day'] ?? ''));
            $text = trim((string) ($row['text'] ?? ''));

            if ($day !== '' || $text !== '') {
                $hours[] = ['day' => $day, 'text' => $text];
            }
        }

        $email  = trim((string) ($settings['email'] ?? ''));
        $phone  = trim((string) ($settings['phone'] ?? ''));
        $digits = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        $logo   = (string) ($settings['logo'] ?? '');
        $label  = trim((string) ($settings['button_label'] ?? ''));

        return static::renderTemplate([
            'logo'        => $logo !== '' ? $context->asset($logo) : '',
            'logoAlt'     => (string) ($settings['logo_alt'] ?? ''),
            'tagline'     => (string) ($settings['tagline'] ?? ''),
            'buttonLabel' => $label,
            'buttonHref'  => self::hrefFor(
                (int) ($settings['button_page'] ?? 0),
                (string) ($settings['button_url'] ?? ''),
                $context
            ),
            'address'     => trim((string) ($settings['address'] ?? '')),
            'phone'       => $phone,
            'phoneHref'   => $digits !== '' ? 'tel:' . $digits : '',
            'email'       => $email,
            'emailHref'   => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? 'mailto:' . $email : '',
            'hours'       => $hours,
            'links'       => $links,
            'copyright'   => str_replace('{år}', date('Y'), (string) ($settings['copyright'] ?? '')),
            'cssVars'     => static::cssVariables($styles),
            'context'     => $context,
        ]);
    }

    private static function hrefFor(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }
}
