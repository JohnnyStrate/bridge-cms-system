<?php
declare(strict_types=1);

/**
 * Finder skabelonerne i temaernes templates-mapper.
 *
 * En skabelon er en side med dummy-indhold, der hører til ét tema:
 *
 *     themes/blaa-tema/templates/KlubforsideTemplate.php
 *         → final class KlubforsideTemplate extends AbstractTemplate
 *
 * Har en skabelon brug for egne filer (et thumbnail), må den gerne have sin
 * egen mappe: themes/<tema>/templates/<mappe>/<Navn>Template.php.
 *
 * Ingen håndholdt liste: en ny skabelon er én ny fil og nul ændringer i
 * fælles filer.
 *
 * SIKKERHED
 * Listen dannes ud fra mapper på disken, ALDRIG ud fra brugerinput. Det,
 * brugeren sender, slås op blandt de fundne slugs.
 *
 * Slug'en er global på tværs af temaer, så den skrives med temaet foran,
 * når den ikke er det blå temas: 'tema1-forside'.
 */
final class TemplateRegistry
{
    /** @var array<string, class-string<TemplateInterface>>|null */
    private static ?array $templates = null;

    /** @var array<string, string> slug => temaets slug */
    private static array $themes = [];

    private function __construct()
    {
    }

    /**
     * Alle skabeloner i alle temaer, sorteret som de skal vises.
     *
     * @return array<string, class-string<TemplateInterface>> slug => klasse
     */
    public static function all(): array
    {
        if (self::$templates !== null) {
            return self::$templates;
        }

        $found = [];

        foreach (array_keys(ThemeRegistry::all()) as $theme) {
            $base  = APP_ROOT . '/themes/' . $theme . '/templates';
            $files = array_merge(
                glob($base . '/*.php') ?: [],
                glob($base . '/*/*.php') ?: []
            );

            foreach ($files as $file) {
                $class = basename($file, '.php');

                if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $class) !== 1) {
                    continue;
                }

                require_once $file;

                // En hjælpefil, der ikke er en skabelon, springes over.
                if (!class_exists($class) || !is_subclass_of($class, TemplateInterface::class)) {
                    continue;
                }

                $slug = $class::slug();

                if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1 || isset($found[$slug])) {
                    error_log("Skabelon '{$class}' har en ugyldig eller optaget slug og blev sprunget over.");
                    continue;
                }

                $found[$slug]        = $class;
                self::$themes[$slug] = $theme;
            }
        }

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

    /** Temaet en skabelon hører til. */
    public static function themeOf(string $slug): string
    {
        self::all();

        return self::$themes[$slug] ?? '';
    }

    /**
     * Skabelonerne i ét tema. "Opret side" viser kun det aktive temas.
     *
     * @return array<string, class-string<TemplateInterface>>
     */
    public static function forTheme(string $themeSlug): array
    {
        return array_filter(
            self::all(),
            static fn (string $slug): bool => self::themeOf($slug) === $themeSlug,
            ARRAY_FILTER_USE_KEY
        );
    }
}
