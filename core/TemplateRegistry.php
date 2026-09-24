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
 * Én mappe pr. skabelon, og filen hedder det samme som klassen:
 *
 *     templates/klubforside/KlubforsideTemplate.php
 *         → final class KlubforsideTemplate extends AbstractTemplate
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

        foreach (glob(APP_ROOT . '/templates/*/*.php') ?: [] as $file) {
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
}
