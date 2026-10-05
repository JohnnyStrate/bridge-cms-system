<?php
declare(strict_types=1);

/**
 * Footer til Standard Bridge 2.
 *
 * Mørk som hero'en, så siden åbner og lukker i samme farve. Indholdet står
 * i sektionernes bredde (--s2-width):
 *
 *   [ logo ]                         Forside  Turneringer  Hold  Kontakt
 *   ───────────────────────────────────────────────────────────────────
 *   KONTAKT            SPILLETIDER              kort tekst om klubben
 *   telefon            Mandag  kl. 18.45        [ Tilmeld ↗ ]
 *   adresse            Onsdag  kl. 13.00
 *   e-mail
 *   ───────────────────────────────────────────────────────────────────
 *   © 2026 Din Bridgeklub                                Til toppen ↑
 *
 * Logo og kontakt kommer fra Indstillinger, så de følger med, når man
 * skifter tema. Knappen og links har temaets hover-streg og skrå pil.
 *
 * Den er temaets globale footer: Standard2Theme::globals() peger på
 * 'standard2-footer' i slot'en 'footer'.
 */
final class Standard2FooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-footer';
    }

    public static function label(): string
    {
        return 'Footer — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Genveje (øverst til højre)',
                'max_rows' => 8,
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
                    ['label' => 'Hold', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'hours' => [
                'type'     => 'repeater',
                'label'    => 'Spilletider (dag, tekst)',
                'max_rows' => 6,
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
                    ['day' => 'Mandag', 'text' => 'kl. 13.00 — eftermiddag'],
                    ['day' => 'Mandag', 'text' => 'kl. 18.45 — aften / åbent hus'],
                    ['day' => 'Juli', 'text' => 'sommerpause'],
                ],
            ],
            'tagline' => [
                'type'    => 'textarea',
                'label'   => 'Kort tekst ved knappen',
                'default' => 'Nye spillere er altid velkomne — også dig, der aldrig har spillet bridge før.',
                'max'     => 300,
            ],
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld',
                'max'         => 30,
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
            'copyright' => [
                'type'    => 'text',
                'label'   => 'Bundtekst ({år} = årstal, {klub} = klubnavn)',
                'default' => '© {år} {klub}',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund',
                'default' => '#211e1e',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#d8d8d8',
                'group'   => 'Farver',
            ],
            'muted_color' => [
                'type'    => 'color',
                'label'   => 'Små overskrifter og bundtekst',
                'default' => '#8f8f8f',
                'group'   => 'Farver',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#f6ecec',
                'group'   => 'Farver',
            ],
            'button_text' => [
                'type'    => 'color',
                'label'   => 'Knap: tekst',
                'default' => '#484848',
                'group'   => 'Farver',
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

        foreach (array_values((array) ($settings['links'] ?? [])) as $index => $link) {
            if (!is_array($link) || trim((string) ($link['label'] ?? '')) === '') {
                continue;
            }

            $links[] = [
                'index' => $index,
                'label' => trim((string) $link['label']),
                'href'  => self::hrefFor((int) ($link['page'] ?? 0), (string) ($link['url'] ?? ''), $context),
            ];
        }

        $hours = [];

        foreach (array_values((array) ($settings['hours'] ?? [])) as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $day  = trim((string) ($row['day'] ?? ''));
            $text = trim((string) ($row['text'] ?? ''));

            if ($day !== '' || $text !== '') {
                $hours[] = ['index' => $index, 'day' => $day, 'text' => $text];
            }
        }

        // Logo og kontakt er klubbens og står under Indstillinger (SiteInfo).
        // E-mail og telefon får deres href bygget her, så et felt med
        // 'javascript:...' aldrig kan ende som et klikbart link.
        $email  = trim(SiteInfo::get('email'));
        $phone  = trim(SiteInfo::get('phone'));
        $digits = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        $logo   = SiteInfo::get('logo');
        $logo   = $logo !== '' ? $logo : 'themes/standard2/assets/logo-placeholder.svg';

        return static::renderTemplate([
            'logo'         => $context->asset($logo),
            'logoAlt'      => SiteInfo::get('club_name'),
            'links'        => $links,
            'address'      => trim(SiteInfo::get('address')),
            'phone'        => $phone,
            'phoneHref'    => $digits !== '' ? 'tel:' . $digits : '',
            'email'        => $email,
            'emailHref'    => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? 'mailto:' . $email : '',
            'hours'        => $hours,
            'tagline'      => (string) ($settings['tagline'] ?? ''),
            'buttonLabel'  => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'   => self::hrefFor(
                (int) ($settings['button_page'] ?? 0),
                (string) ($settings['button_url'] ?? ''),
                $context
            ),
            'copyright'    => SiteInfo::expand((string) ($settings['copyright'] ?? '')),
            'copyrightRaw' => (string) ($settings['copyright'] ?? ''),
            'cssVars'      => static::cssVariables($styles),
            'context'      => $context,
        ]);
    }

    /** Side før adresse — en valgt side overlever, at den skifter slug. */
    private static function hrefFor(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }
}
