<?php
declare(strict_types=1);

/**
 * Finder temaerne i /themes/ og ved, hvilket der er aktivt.
 *
 * Ingen håndholdt liste: et tema er en mappe med en *Theme.php-klasse.
 *
 *     themes/tema1/Tema1Theme.php  →  slug 'tema1'
 *
 * SIKKERHED
 * Listen dannes ud fra mapper på disken, aldrig ud fra brugerinput. En slug
 * fra databasen eller en formular slås op her, og findes den ikke, bruges
 * standardtemaet.
 */
final class ThemeRegistry
{
    /** @var array<string, class-string<ThemeInterface>>|null */
    private static ?array $themes = null;

    private static ?string $active = null;

    private function __construct()
    {
    }

    /**
     * Alle temaer, sorteret efter sortOrder() og navn.
     *
     * @return array<string, class-string<ThemeInterface>> slug => klasse
     */
    public static function all(): array
    {
        if (self::$themes !== null) {
            return self::$themes;
        }

        $found = [];

        foreach (glob(APP_ROOT . '/themes/*/*Theme.php') ?: [] as $file) {
            $class  = basename($file, '.php');
            $folder = basename(dirname($file));

            // Klassenavnet bliver til en sti i autoloaderen, og mappenavnet
            // ender i URL'er og i databasen. Begge holdes stramme.
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $class) !== 1
                || preg_match('/^[a-z0-9-]+$/', $folder) !== 1) {
                error_log("Tema i '{$folder}' sprunget over: ugyldigt navn.");
                continue;
            }

            require_once $file;

            if (!class_exists($class) || !is_subclass_of($class, ThemeInterface::class)) {
                continue;
            }

            $found[$folder] = $class;
        }

        uasort($found, static function (string $a, string $b): int {
            return [$a::sortOrder(), $a::name()] <=> [$b::sortOrder(), $b::name()];
        });

        return self::$themes = $found;
    }

    /** @return class-string<ThemeInterface>|null */
    public static function get(string $slug): ?string
    {
        return self::all()[$slug] ?? null;
    }

    public static function exists(string $slug): bool
    {
        return self::get($slug) !== null;
    }

    /** Standardtemaet: det første i rækken. */
    public static function defaultSlug(): string
    {
        return (string) (array_key_first(self::all()) ?? '');
    }

    /**
     * Det aktive tema på sitet.
     *
     * Står der et tema i databasen, som ikke findes længere (mappen er
     * slettet), bruges standardtemaet frem for at vælte hele sitet.
     */
    public static function active(PDO $pdo): string
    {
        if (self::$active !== null) {
            return self::$active;
        }

        try {
            $slug = (new SiteSettingsRepository($pdo))->get('active_theme', '');
        } catch (PDOException $e) {
            // 42S02 = tabellen findes ikke. Så mangler migrationen, og det
            // skal stå tydeligt i stedet for en rå databasefejl.
            if ($e->getCode() === '42S02') {
                throw new RuntimeException(
                    'Tabellen site_settings mangler. Kør database/migrations/2026-09-24_temaer.sql.',
                    0,
                    $e
                );
            }
            throw $e;
        }

        return self::$active = self::exists($slug) ? $slug : self::defaultSlug();
    }

    /**
     * Gemmer det aktive tema. Kalderen står for at oprette temaets
     * navbar og footer — se admin/activate-theme.php.
     */
    public static function setActive(PDO $pdo, string $slug): void
    {
        if (!self::exists($slug)) {
            throw new InvalidArgumentException('Temaet findes ikke.');
        }

        (new SiteSettingsRepository($pdo))->set('active_theme', $slug);
        self::$active = $slug;
    }
}
