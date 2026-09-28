<?php
declare(strict_types=1);

/**
 * Fælles for Bridge Card-temaets blokke: farver, standardbilleder, former
 * og ikoner, links, animationer og temaets fælles stylesheet.
 *
 * Ligger i temaets rod, så autoloaderen finder den. Filnavnet slutter
 * ikke på Theme.php, så ThemeRegistry ser den ikke som et tema.
 */
final class BridgeCardKit
{
    /**
     * Temaets farver. Blokkene bruger dem som standardværdier i Udseende,
     * og styles skrives altid som CSS-variabler på blokken — så en farve
     * står kun her, ikke også i CSS'en.
     */
    public const COLORS = [
        'button'     => '#ed453c',   // alle røde knapper og kanter
        'teal_light' => '#88e1d1',   // gradient, top
        'teal'       => '#70c3b4',   // gradient, bund + turkise flader
        'teal_text'  => '#6cc7b8',   // turkis tekst og pynt
        'blue'       => '#2c4f96',   // blå overskrifter, ikoner og pynt
        'blue_bg'    => '#1b3f8a',   // den blå sektion
        'dark'       => '#1c1c1c',   // footer og kløveren i heroen
        'white'      => '#ffffff',
        'grey'       => '#6f6f6f',   // brødtekst i kort
    ];

    /**
     * Standardbilleder. Skift billedet her, så følger alle blokke med.
     * Hold dem web-venlige (ca. 2400 px bredt, JPG/WebP).
     */
    public const PHOTO       = 'themes/bridgecardtheme/assets/bgcardtheme.jpg';
    public const CARDS_IMAGE = 'themes/bridgecardtheme/assets/cardspicture.webp';

    /** Kulørerne. Formerne selv står i assets/bridgecard.css. */
    public const SUITS = ['Kløver', 'Ruder', 'Hjerter', 'Spar'];

    /** Ikoner til kort: temaets egne + kulørerne. */
    public const ICONS = ['Kalender', 'Personer', 'Liste', 'Kløver', 'Ruder', 'Hjerter', 'Spar', 'Ingen'];

    private const STYLESHEET = 'themes/bridgecardtheme/assets/bridgecard.css';

    private function __construct()
    {
    }

    /* --- Stylesheet og animation ---------------------------------------- */

    /**
     * <link> til temaets fælles stylesheet. Står øverst i hver blok, så
     * filen altid er med — også i eksporten, der kopierer de filer, HTML'en
     * peger på. Browseren henter den kun én gang.
     */
    public static function stylesheet(RenderContext $context): string
    {
        $href = $context->asset(self::STYLESHEET)
            . ($context->isEditor() ? PageRenderer::cacheBuster(self::STYLESHEET) : '');

        return '<link rel="stylesheet" href="' . e($href) . '">';
    }

    /**
     * Scriptet, der starter animationerne for sektionen lige før det.
     * Samme mønster som tema 2 og 3: .bc-armed skjuler, .is-visible viser.
     * Ikke i editoren, og ikke ved reduceret bevægelse.
     */
    public static function revealScript(RenderContext $context): string
    {
        if ($context->isInlineEditing()) {
            return '';
        }

        return <<<'HTML'
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('bc-armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -15% 0px' }).observe(section);
}());
</script>
HTML;
    }

    /**
     * data-value til en form eller et ikon (.bc-mask), og i editoren de
     * attributter, editor.js bruger til at skifte den live (data-live).
     *
     *   BridgeCardKit::live($context, 'suit', $suit)
     *   BridgeCardKit::live($context, 'icon', $icon, 'cards', 2)
     */
    public static function live(
        RenderContext $context,
        string $field,
        string $value,
        ?string $repeater = null,
        ?int $row = null
    ): string {
        $html = ' data-value="' . e($value) . '"';

        if ($context->isInlineEditing()) {
            $html .= ' data-live="' . e($field) . '"';

            if ($repeater !== null) {
                $html .= ' data-live-repeater="' . e($repeater) . '"'
                    . ' data-live-row="' . (int) $row . '"';
            }
        }

        return $html;
    }

    /** En værdi fra en liste, eller listens første, hvis den er ukendt. */
    public static function pick(mixed $value, array $options): string
    {
        return in_array($value, $options, true) ? (string) $value : (string) $options[0];
    }

    /* --- Felter ------------------------------------------------------- */

    /** Et valg af kulør/form til Udseende. */
    public static function suitField(string $label, string $default = 'Kløver', string $group = 'Form'): array
    {
        return [
            'type'    => 'select',
            'label'   => $label,
            'default' => $default,
            'options' => self::SUITS,
            'group'   => $group,
        ];
    }

    /** Knappens farve — samme standard i alle blokke. */
    public static function buttonColorField(): array
    {
        return [
            'type'    => 'color',
            'label'   => 'Knap',
            'default' => self::COLORS['button'],
            'group'   => 'Knap',
        ];
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

    /* --- Links -------------------------------------------------------- */

    /** Side før adresse. Intet valgt = '#'. */
    public static function href(int $pageId, string $url, RenderContext $context): string
    {
        if ($pageId > 0) {
            return $context->pageUrl($pageId);
        }

        return $url !== '' ? $url : '#';
    }

    /** Knappens adresse ud fra buttonFields(). */
    public static function buttonHref(array $settings, RenderContext $context): string
    {
        return self::href(
            (int) ($settings['button_page'] ?? 0),
            (string) ($settings['button_url'] ?? ''),
            $context
        );
    }

    /**
     * Links fra en repeater, klar til templaten. Rækker uden tekst springes
     * over (undtagen i editoren, så de kan udfyldes).
     *
     * @return array<int, array{index: int, label: string, href: string, page: int}>
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
                'page'  => (int) ($row['page'] ?? 0),
                'href'  => self::href((int) ($row['page'] ?? 0), (string) ($row['url'] ?? ''), $context),
            ];
        }

        return $links;
    }

    /**
     * Rækker fra en repeater med deres nummer, så templaten kan koble
     * inline-redigering til den rigtige række.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function rows(mixed $rows): array
    {
        $result = [];

        foreach (array_values((array) $rows) as $index => $row) {
            if (is_array($row)) {
                $result[] = ['index' => $index] + $row;
            }
        }

        return $result;
    }
}
