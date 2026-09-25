<?php
declare(strict_types=1);

/**
 * Navbar til tema 2.
 *
 * Logo i venstre side og menuen i en gradient-pille midt på siden. Det
 * aktive menupunkt står med fed skrift og får en lille afrundet streg
 * under sig.
 *
 * Ligger navbaren lige over en tema 2-hero, lægger den sig oven på
 * hero-billedet (se block.css). Står den over noget andet, fylder den
 * bare sin egen plads.
 *
 * Den er tema 2's globale navbar: Tema2Theme::globals() peger på
 * 'tema2-navbar' i slot'en 'header'.
 */
final class Tema2NavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema2-navbar';
    }

    public static function label(): string
    {
        return 'Navbar — tema 2';
    }

    public static function getSchema(): array
    {
        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter',
                'max_rows' => 12,
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
                    ['label' => 'Resultater', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'gradient_start' => [
                'type'    => 'color',
                'label'   => 'Menu: farve til venstre',
                'default' => '#192e3c',
                'group'   => 'Farver',
            ],
            'gradient_end' => [
                'type'    => 'color',
                'label'   => 'Menu: farve til højre',
                'default' => '#457ba2',
                'group'   => 'Farver',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekst',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'indicator_color' => [
                'type'    => 'color',
                'label'   => 'Streg under aktiv side',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'link_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 20,
                'min'     => 12,
                'max'     => 32,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Afrunding (menu og logo)',
                'default' => 12,
                'min'     => 0,
                'max'     => 40,
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
        $current = $context->currentPageId();
        $links   = [];
        $linked  = false;

        foreach ((array) ($settings['links'] ?? []) as $link) {
            if (!is_array($link)) {
                continue;
            }

            $text = trim((string) ($link['label'] ?? ''));

            // Et menupunkt uden tekst ville være en usynlig klikflade.
            if ($text === '') {
                continue;
            }

            $pageId = (int) ($link['page'] ?? 0);
            $linked = $linked || $pageId > 0;

            $links[] = [
                'label'  => $text,
                'href'   => self::hrefFor($pageId, (string) ($link['url'] ?? ''), $context),
                'active' => $pageId > 0 && $pageId === $current,
            ];
        }

        // Er INGEN menupunkter koblet til en side endnu — altså ren
        // dummy-menu — markeres det første som aktivt. Ellers kan man ikke
        // se designets vigtigste detalje, før man har bygget sine sider.
        if (!$linked && $links !== []) {
            $links[0]['active'] = true;
        }

        // Klubbens logo fra Indstillinger. Er der intet, vises temaets
        // pladsholder, så man kan se, hvor logoet kommer til at stå.
        $logo = SiteInfo::get('logo');
        $logo = $logo !== '' ? $logo : 'themes/tema2/assets/logo-placeholder.svg';

        return static::renderTemplate([
            'logo'     => $logo !== '' ? $context->asset($logo) : '',
            'logoAlt'  => SiteInfo::get('club_name'),
            'homeHref' => $links[0]['href'] ?? '#',
            'links'    => $links,
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
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
