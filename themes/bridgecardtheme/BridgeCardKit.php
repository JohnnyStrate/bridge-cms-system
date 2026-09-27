<?php
declare(strict_types=1);

/**
 * Fælles hjælpere til Bridge Card-temaets blokke: ikoner og links.
 *
 * Ligger i temaets rod, så autoloaderen finder den. Filnavnet slutter
 * ikke på Theme.php, så ThemeRegistry ser den ikke som et tema.
 */
final class BridgeCardKit
{
    /**
     * Ikoner til kortene (viewBox 0 0 24 24). 'currentColor' er ikonfarven.
     * Et nyt ikon er én linje mere her.
     */
    public const ICONS = [
        'Kalender' => '<rect x="2" y="3.5" width="20" height="18.5" rx="2.5" fill="currentColor"/>'
            . '<path d="M2 9h20" stroke="#fff" stroke-width="1.6"/>'
            . '<path d="M7 1.5v4M17 1.5v4" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
        'Personer' => '<circle cx="9" cy="7.5" r="3.6" fill="currentColor"/>'
            . '<circle cx="17.5" cy="8.5" r="2.8" fill="currentColor"/>'
            . '<path d="M2 20c0-4 3.1-6.8 7-6.8s7 2.8 7 6.8z" fill="currentColor"/>'
            . '<path d="M16.5 20c0-2.6-.8-4.6-2.3-5.8a5.6 5.6 0 0 1 8.3 5.8z" fill="currentColor"/>',
        'Liste' => '<path d="M4 3l.9 1.9 2.1.3-1.5 1.4.4 2.1L4 7.7 2.1 8.7l.4-2.1L1 5.2l2.1-.3z" fill="currentColor"/>'
            . '<path d="M10 6h13M10 12.5h13M10 19h13" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>'
            . '<circle cx="4" cy="12.5" r="1.7" fill="currentColor"/><circle cx="4" cy="19" r="1.7" fill="currentColor"/>',
        'Kløver' => self::CLUB,
        'Spar'   => self::SPADE,
        'Hjerter' => '<path d="M12 21s-9-5.6-9-11.6A5 5 0 0 1 12 6.6a5 5 0 0 1 9 2.8C21 15.4 12 21 12 21z" fill="currentColor"/>',
        'Ingen'  => '',
    ];

    public const CLUB = '<circle cx="12" cy="6.5" r="4.5" fill="currentColor"/>'
        . '<circle cx="6.5" cy="13" r="4.5" fill="currentColor"/>'
        . '<circle cx="17.5" cy="13" r="4.5" fill="currentColor"/>'
        . '<path d="M12 10l-2.4 12h4.8z" fill="currentColor"/>';

    public const SPADE = '<path d="M12 2s-9 6.5-9 12a4.6 4.6 0 0 0 7.7 3.4L9.4 22h5.2l-1.3-4.6A4.6 4.6 0 0 0 21 14c0-5.5-9-12-9-12z" fill="currentColor"/>';

    public const PHONE = '<path d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z" fill="currentColor"/>';

    public const HOUSE = '<path d="M12 2.5 1.5 11.5h3V21h5.5v-6h4v6h5.5v-9.5h3z" fill="currentColor"/>';

    private function __construct()
    {
    }

    /** Side før adresse. Intet valgt = '#'. */
    public static function href(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }

    /** Standardfelter til en knap: tekst, side og ekstern adresse. */
    public static function buttonFields(string $default): array
    {
        return [
            'button_label' => [
                'type'        => 'text',
                'label'       => 'Knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => $default,
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
        ];
    }

    /** Link-felter til en række i en repeater. */
    public static function linkRowFields(): array
    {
        return [
            'label' => ['type' => 'text', 'label' => 'Tekst', 'default' => '', 'max' => 40],
            'page'  => ['type' => 'page', 'label' => 'Side', 'default' => 0],
            'url'   => ['type' => 'url', 'label' => 'Ekstern adresse', 'default' => ''],
        ];
    }

    /**
     * Links fra en repeater, klar til templaten. Rækker uden tekst springes over.
     *
     * @return array<int, array{index: int, label: string, href: string}>
     */
    public static function links(mixed $rows, RenderContext $context): array
    {
        $links = [];

        foreach (array_values((array) $rows) as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));

            if ($label === '' && !$context->isInlineEditing()) {
                continue;
            }

            $links[] = [
                'index' => $index,
                'label' => $label,
                'href'  => self::href((int) ($row['page'] ?? 0), (string) ($row['url'] ?? ''), $context),
            ];
        }

        return $links;
    }

    /** Farvefelter til en baggrund, der kan være en gradient (top → bund). */
    public static function backgroundFields(string $top, string $bottom): array
    {
        return [
            'bg_top' => [
                'type'    => 'color',
                'label'   => 'Baggrund: top',
                'default' => $top,
                'group'   => 'Baggrund',
            ],
            'bg_bottom' => [
                'type'    => 'color',
                'label'   => 'Baggrund: bund (samme farve = ingen gradient)',
                'default' => $bottom,
                'group'   => 'Baggrund',
            ],
        ];
    }
}
