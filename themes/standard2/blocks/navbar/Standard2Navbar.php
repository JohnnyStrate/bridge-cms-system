<?php
declare(strict_types=1);

/**
 * Navbar til Standard Bridge 2.
 *
 * Menupunkterne står til venstre, i flugt med hero'ens indhold. Det aktive
 * punkt er lysere og står i en tynd, hvid ramme. Ved hover glider en tynd
 * streg ind under teksten fra venstre.
 *
 * Ligger navbaren over en hero, er den gennemsigtig og ligger oven på
 * billedet. Scroller man ned, "popper" den frem som en fast bjælke øverst
 * (se scriptet i template.php).
 *
 * Den er temaets globale navbar: Standard2Theme::globals() peger på
 * 'standard2-navbar' i slot'en 'header'.
 */
final class Standard2NavbarBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-navbar';
    }

    public static function label(): string
    {
        return 'Navbar — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'links' => [
                'type'     => 'repeater',
                'label'    => 'Menupunkter',
                'max_rows' => 10,
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
                    ['label' => 'Turneringer', 'page' => 0, 'url' => '#'],
                    ['label' => 'Hold', 'page' => 0, 'url' => '#'],
                    ['label' => 'Kontakt', 'page' => 0, 'url' => '#'],
                ],
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'link_color' => [
                'type'    => 'color',
                'label'   => 'Menupunkter',
                'default' => '#8f8f8f',
                'group'   => 'Farver',
            ],
            'active_color' => [
                'type'    => 'color',
                'label'   => 'Aktivt menupunkt',
                'default' => '#d8d8d8',
                'group'   => 'Farver',
            ],
            'frame_color' => [
                'type'    => 'color',
                'label'   => 'Ramme om aktivt punkt',
                'default' => '#ffffff',
                'group'   => 'Farver',
            ],
            'bar_color' => [
                'type'    => 'color',
                'label'   => 'Bjælken, når man har scrollet',
                'default' => '#211e1e',
                'group'   => 'Farver',
            ],
            'link_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse',
                'default' => 22,
                'min'     => 12,
                'max'     => 32,
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
        $links  = [];
        $linked = false;

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
        // vises som aktivt, så man kan se rammen, før siderne er bygget.
        if (!$linked && $links !== []) {
            $links[0]['active'] = true;
        }

        // Den side, man står på — eller dens hovedside (se
        // RenderContext::activeLinkIndex()).
        $active = $context->activeLinkIndex(array_column($links, 'pageId'));

        if ($active !== null) {
            $links[$active]['active'] = true;
        }

        return static::renderTemplate([
            'links'   => $links,
            'cssVars' => static::cssVariables($styles),
            'context' => $context,
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
