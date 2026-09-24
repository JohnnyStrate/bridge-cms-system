<?php
declare(strict_types=1);

/**
 * Finder skabelonerne i /templates/.
 *
 * Til forskel fra BlockRegistry er der ingen håndholdt liste. Registryet
 * scanner mappen og tager de klasser, der følger TemplateInterface. En ny
 * skabelon er derfor én ny mappe og nul ændringer i fælles filer — så to
 * personer kan lave hver sin skabelon uden at få konflikter i git.
 *
 * SIKKERHED
 * Listen dannes ud fra mapper på disken, ALDRIG ud fra brugerinput. Det,
 * brugeren sender, slås op blandt de fundne slugs, præcis som en
 * block_type slås op i BlockRegistry. En manipuleret værdi kan derfor
 * ikke pege på en vilkårlig fil.
 *
 * KONVENTION
 * Skabelonerne ligger i temamappen, og filen hedder det samme som klassen:
 *
 *     templates/blaa-tema/KlubforsideTemplate.php
 *         → final class KlubforsideTemplate extends AbstractTemplate
 *
 * Har en skabelon brug for sine egne filer (et thumbnail, en delfil), må den
 * gerne få sin egen mappe inde i temaet:
 *
 *     templates/blaa-tema/klubforside/KlubforsideTemplate.php
 *
 * Begge dybder findes automatisk.
 */
final class TemplateRegistry
{
    /** @var array<string, class-string<TemplateInterface>>|null */
    private static ?array $templates = null;

    private function __construct()
    {
    }

    /**
     * Alle skabeloner, sorteret som de skal vises i "Opret side".
     *
     * @return array<string, class-string<TemplateInterface>> slug => klasse
     */
    public static function all(): array
    {
        if (self::$templates !== null) {
            return self::$templates;
        }

        $found = [];

        // templates/<tema>/Navn.php og templates/<tema>/<mappe>/Navn.php.
        $files = array_merge(
            glob(APP_ROOT . '/templates/*/*.php') ?: [],
            glob(APP_ROOT . '/templates/*/*/*.php') ?: []
        );

        foreach ($files as $file) {
            $class = basename($file, '.php');

            // Klassenavnet kommer fra et filnavn, vi selv har fundet.
            // Guarden er med, fordi autoloaderen laver navnet til en sti
            // igen, og den slags skal aldrig kunne pege uden for projektet.
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $class) !== 1) {
                continue;
            }

            require_once $file;

            // En hjælpefil i mappen, der ikke er en skabelon, springes
            // over frem for at vælte "Opret side".
            if (!class_exists($class) || !is_subclass_of($class, TemplateInterface::class)) {
                continue;
            }

            $slug = $class::slug();

            if ($slug === '' || isset($found[$slug])) {
                error_log("Skabelon '{$class}' har en tom eller optaget slug og blev sprunget over.");
                continue;
            }

            $found[$slug] = $class;
        }

        // Lavest sortOrder først, derefter alfabetisk på navnet, så
        // rækkefølgen er den samme hver gang uanset filsystemet.
        uasort($found, static function (string $a, string $b): int {
            return [$a::sortOrder(), $a::name()] <=> [$b::sortOrder(), $b::name()];
        });

        return self::$templates = $found;
    }

    /**
     * @return class-string<TemplateInterface>|null
     */
    public static function get(string $slug): ?string
    {
        return self::all()[$slug] ?? null;
    }

    public static function exists(string $slug): bool
    {
        return self::get($slug) !== null;
    }

    /**
     * Skabelonerne delt op i temaer, til "Opret side".
     *
     * Temaet læses af mappen, skabelonen ligger i — samme regel som for
     * blokkene, så de to lister bruger de samme overskrifter.
     *
     * @return array<string, array<string, class-string<TemplateInterface>>>
     */
    public static function grouped(): array
    {
        $templates = self::all();
        $folders   = [];
        $labels    = [];

        foreach ($templates as $slug => $class) {
            $folders[$slug] = Themes::ofClass($class, 'templates');
            $labels[$slug]  = $class::name();
        }

        $grouped = [];

        // Themes::group() grupperer navne. Her skal klasserne med videre,
        // så grupperingen genbruges og nøglerne oversættes tilbage.
        foreach (Themes::group($labels, $folders) as $theme => $group) {
            foreach (array_keys($group) as $slug) {
                $grouped[$theme][$slug] = $templates[$slug];
            }
        }

        return $grouped;
    }
}
