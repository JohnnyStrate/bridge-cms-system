<?php
declare(strict_types=1);

/**
 * Navbar til tema 3.
 *
 * En blød, rød gradient-bjælke øverst og klubbens logo centreret under
 * den. Den aktive side er fed, bliver skubbet lidt ned og får en ekstra
 * rød "flap", der hænger ud under bjælken — med en lille grå prik under
 * teksten.
 *
 * Den er tema 3's globale navbar: Tema3Theme::globals() peger på
 * 'tema3-navbar' i slot'en 'header'.
 */
final class Tema3NavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'tema3-navbar';
    }

    public static function label(): string
    {
        return 'Navbar — tema 3';
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
                'label'   => 'Bjælke: farve til venstre',
                'default' => '#cc5656',
                'group'   => 'Farver',
            ],
            'gradient_end' => [
                'type'    => 'color',
                'label'   => 'Bjælke: farve til højre',
                'default' => '#ca6a6a',
                'group'   => 'Farver',
            ],
            'tab_color' => [
                'type'    => 'color',
                'label'   => 'Flappen under aktiv side',
                'default' => '#cc5656',
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
                'label'   => 'Prik under aktiv side',
                'default' => '#c7c7c7',
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
                'label'   => 'Afrunding',
                'default' => 28,
                'min'     => 0,
                'max'     => 60,
                'unit'    => 'px',
                'group'   => 'Form',
            ],
            'logo_size' => [
                'type'    => 'number',
                'label'   => 'Logoets højde',
                'default' => 110,
                'min'     => 40,
                'max'     => 220,
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
                'pageId' => $pageId,
                'active' => false,
            ];
        }

        // Ren dummy-menu (intet punkt koblet til en side): det første punkt
        // vises som aktivt, så man kan se flappen, før siderne er bygget.
        if (!$linked && $links !== []) {
            $links[0]['active'] = true;
        }

        // Den side, man står på — eller dens hovedside, hvis siden selv
        // ikke er i menuen (se RenderContext::activeLinkIndex()).
        $active = $context->activeLinkIndex(array_column($links, 'pageId'));

        if ($active !== null) {
            $links[$active]['active'] = true;
        }

        // Klubbens logo fra Indstillinger — ellers temaets pladsholder.
        $logo = SiteInfo::get('logo');
        $logo = $logo !== '' ? $logo : 'themes/tema3/assets/logo-placeholder.svg';

        return static::renderTemplate([
            'logo'     => $context->asset($logo),
            'logoAlt'  => SiteInfo::get('club_name'),
            'homeHref' => $links[0]['href'] ?? '#',
            'links'    => $links,
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
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
