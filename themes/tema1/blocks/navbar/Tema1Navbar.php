<?php
declare(strict_types=1);

/**
 * Navbar til tema 1.
 *
 * Samme opgave som det blå temas navbar — logo og menupunkter — men et
 * helt andet udseende: lys bjælke, skrå underkant, links som piller og en
 * fremhævet knap yderst til højre.
 *
 * Den er tema 1's globale navbar: Tema1Theme::globals() peger på
 * 'tema1-navbar' i slot'en 'header'.
 *
 * Adresserne regnes ud her i render(), ikke i templaten — samme regel som i
 * den blå navbar: side før url, fordi den interne henvisning overlever, at
 * målsiden får en ny slug.
 */
final class Tema1NavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema1-navbar';
    }

    public static function label(): string
    {
        return 'Navbar — tema 1';
    }

    public static function getSchema(): array
    {
        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter',
                'max_rows' => 30,
                'fields'   => [
                    'label' => [
                        'type'        => 'text',
                        'label'       => 'Tekst',
                        'placeholder' => 'Fx Kontakt',
                        'default'     => '',
                    ],
                    'page' => [
                        'type'    => 'page',
                        'label'   => 'Side',
                        'default' => 0,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Ekstern adresse',
                        'placeholder' => 'Indsæt link',
                        'default'     => '',
                    ],
                ],
                'default' => [
                    ['label' => 'Forside', 'page' => 0, 'url' => '#'],
                    ['label' => 'Om klubben', 'page' => 0, 'url' => '#'],
                    ['label' => 'Turneringer', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
            'cta_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Bliv medlem',
            ],
            'cta_page' => [
                'type'    => 'page',
                'label'   => 'Knap: side',
                'default' => 0,
            ],
            'cta_url' => [
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
            'link_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 16,
                'min'     => 10,
                'max'     => 32,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            // Hvor meget den skrå underkant skærer. 0 = lige kant.
            'slant' => [
                'type'    => 'number',
                'label'   => 'Skrå underkant',
                'default' => 28,
                'min'     => 0,
                'max'     => 80,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'bar_radius' => [
                'type'    => 'number',
                'label'   => 'Afrunding på piller',
                'default' => 999,
                'min'     => 0,
                'max'     => 999,
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

            // Et menupunkt uden tekst ville være en usynlig klikflade.
            if ($text === '') {
                continue;
            }

            $links[] = [
                'label' => $text,
                'href'  => self::hrefFor(
                    (int) ($link['page'] ?? 0),
                    (string) ($link['url'] ?? ''),
                    $context
                ),
            ];
        }

        // Logo og klubnavn står under Indstillinger (SiteInfo). Uden logo
        // vises navnet med første bogstav som mærke.
        $logo      = SiteInfo::get('logo');
        $ctaLabel  = trim((string) ($settings['cta_label'] ?? ''));

        return static::renderTemplate([
            'logo'      => $logo !== '' ? $context->asset($logo) : '',
            'logoAlt'   => SiteInfo::get('club_name'),
            'brandText' => trim(SiteInfo::get('club_name')),
            'links'     => $links,
            'ctaLabel'  => $ctaLabel,
            'ctaHref'   => $ctaLabel === '' ? '' : self::hrefFor(
                (int) ($settings['cta_page'] ?? 0),
                (string) ($settings['cta_url'] ?? ''),
                $context
            ),
            'cssVars'   => static::cssVariables($styles),
        ]);
    }

    /**
     * Side før adresse. En valgt side overlever, at målsiden får en ny
     * slug; en håndskrevet adresse gør ikke.
     */
    private static function hrefFor(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }
}
