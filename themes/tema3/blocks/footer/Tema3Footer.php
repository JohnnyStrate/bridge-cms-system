<?php
declare(strict_types=1);

/**
 * Footer til tema 3.
 *
 * En blød, lys "boble" med store runde hjørner foroven, der toner fra hvid
 * ned i sidens rosa — samme farve, som siden starter med øverst. Står den
 * lige under "Linkkort", lægger den sig en anelse op over den røde boks,
 * så hjørnerne ses.
 *
 * Indhold:
 *   - klubbens logo, en kort tekst og en knap (samme hover som hero'en),
 *   - tre kolonner: kontakt (fra Indstillinger), spilletider og genveje,
 *   - en rød bundbjælke — navbarens gradient — med © og "Til toppen".
 *
 * Den er tema 3's globale footer: Tema3Theme::globals() peger på
 * 'tema3-footer' i slot'en 'footer'.
 *
 * E-mail og telefon får deres href bygget her, så et felt med
 * 'javascript:...' aldrig kan ende som et klikbart link.
 */
final class Tema3FooterBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-footer';
    }

    public static function label(): string
    {
        return 'Footer — tema 3';
    }

    public static function getSchema(): array
    {
        return [
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
                'label'   => 'Bundtekst ({år} = årstal, {klub} = klubnavn)',
                'default' => '© {år} {klub}',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'bg_top' => [
                'type'    => 'color',
                'label'   => 'Baggrund øverst',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'bg_bottom' => [
                'type'    => 'color',
                'label'   => 'Baggrund nederst',
                'default' => '#efb7b7',
                'group'   => 'Farver',
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Overskrifter i kolonnerne',
                'default' => '#cc5656',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#4d4848',
                'group'   => 'Farver',
            ],
            'bar_start' => [
                'type'    => 'color',
                'label'   => 'Bundbjælke: farve til venstre',
                'default' => '#cc5656',
                'group'   => 'Farver',
            ],
            'bar_end' => [
                'type'    => 'color',
                'label'   => 'Bundbjælke: farve til højre',
                'default' => '#ca6a6a',
                'group'   => 'Farver',
            ],
            'bar_text' => [
                'type'    => 'color',
                'label'   => 'Bundbjælke: tekst',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knap',
                'default' => '#bcb6b6',
                'group'   => 'Knap',
            ],
            'button_hover' => [
                'type'    => 'color',
                'label'   => 'Knap ved hover',
                'default' => '#e36d6d',
                'group'   => 'Knap',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Afrunding af footerens hjørner',
                'default' => 56,
                'min'     => 0,
                'max'     => 120,
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
        $email  = trim(SiteInfo::get('email'));
        $phone  = trim(SiteInfo::get('phone'));
        $digits = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        $logo   = SiteInfo::get('logo');
        $logo   = $logo !== '' ? $logo : 'themes/tema3/assets/logo-placeholder.svg';

        return static::renderTemplate([
            'logo'         => $context->asset($logo),
            'logoAlt'      => SiteInfo::get('club_name'),
            'tagline'      => (string) ($settings['tagline'] ?? ''),
            'buttonLabel'  => trim((string) ($settings['button_label'] ?? '')),
            'buttonHref'   => self::hrefFor(
                (int) ($settings['button_page'] ?? 0),
                (string) ($settings['button_url'] ?? ''),
                $context
            ),
            'address'      => trim(SiteInfo::get('address')),
            'phone'        => $phone,
            'phoneHref'    => $digits !== '' ? 'tel:' . $digits : '',
            'email'        => $email,
            'emailHref'    => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? 'mailto:' . $email : '',
            'hours'        => $hours,
            'links'        => $links,
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
