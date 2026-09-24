<?php
declare(strict_types=1);

/**
 * Oversætter en block_type-streng fra databasen til den PHP-klasse,
 * der kan tegne blokken.
 *
 * Det er systemets allowlist. En værdi fra databasen bliver ALDRIG brugt
 * til at bygge en filsti direkte — den slås op i listen nedenfor, og
 * findes den ikke, sker der ingenting.
 *
 * HVOR LISTEN KOMMER FRA
 *   - SHARED nedenfor: værktøjsblokke, alle temaer kan bruge (blocks/faelles/).
 *   - Hvert temas blocks() i themes/<tema>/<Navn>Theme.php.
 * En ny blok i et tema skrives altså ind i temaets egen klasse, ikke her.
 * Så kan to personer lave hvert sit tema uden konflikter i git.
 *
 * Alle temaers blokke er kendte, også de inaktive temaers. En side, der
 * blev bygget i det blå tema, kan derfor stadig tegnes efter et temaskift.
 *
 * NAVNGIVNING
 * Nøglen er databasenøglen. Den står i page_blocks.block_type og må ikke
 * ændres uden en migration. Blokke i nye temaer får temaet foran:
 * 'tema1-hero' og klassen Tema1HeroBlock.
 */
final class BlockRegistry
{
    /** @var array<string, class-string<BlockInterface>> */
    private const SHARED = [
        'image'    => ImageBlock::class,
        'textarea' => TextAreaBlock::class,
        'gallery'  => GalleryBlock::class,
    ];

    /** @var array<string, class-string<BlockInterface>>|null */
    private static ?array $blocks = null;

    /** @var array<string, string>|null bloktype => temaets slug ('' = fælles) */
    private static ?array $owners = null;

    private function __construct()
    {
    }

    /** @return array<string, class-string<BlockInterface>> */
    private static function map(): array
    {
        if (self::$blocks !== null) {
            return self::$blocks;
        }

        $blocks = self::SHARED;
        $owners = array_fill_keys(array_keys(self::SHARED), '');

        foreach (ThemeRegistry::all() as $slug => $theme) {
            foreach ($theme::blocks() as $type => $class) {
                // To temaer med samme type ville gøre det tilfældigt, hvilken
                // blok der tegnes. Den første vinder, og fejlen logges.
                if (isset($blocks[$type])) {
                    error_log("Bloktypen '{$type}' i temaet '{$slug}' findes allerede og er sprunget over.");
                    continue;
                }

                $blocks[$type] = $class;
                $owners[$type] = $slug;
            }
        }

        self::$owners = $owners;

        return self::$blocks = $blocks;
    }

    /**
     * @return class-string<BlockInterface>|null
     */
    public static function get(string $type): ?string
    {
        $class = self::map()[$type] ?? null;

        // Findes filen ikke, må vi hellere svare "ukendt blok" end at
        // lade en fatal fejl vælte hele siden.
        if ($class === null || !class_exists($class)) {
            return null;
        }

        return $class;
    }

    public static function exists(string $type): bool
    {
        return self::get($type) !== null;
    }

    /** Temaet en bloktype hører til. '' = fælles blok eller ukendt. */
    public static function themeOf(string $type): string
    {
        self::map();

        return self::$owners[$type] ?? '';
    }

    /**
     * Alle bloktyper med deres visningsnavn.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $result = [];

        foreach (array_keys(self::map()) as $type) {
            $class = self::get($type);

            if ($class !== null) {
                $result[$type] = $class::label();
            }
        }

        return $result;
    }

    /**
     * Blokkene editorens "+"-menu tilbyder: det aktive temas egne blokke
     * og derefter de fælles. Andre temaers blokke er ikke med.
     *
     * @return array<string, array<string, string>> overskrift => (type => navn)
     */
    public static function grouped(string $themeSlug): array
    {
        $theme  = ThemeRegistry::get($themeSlug);
        $own    = [];
        $shared = [];

        foreach (self::all() as $type => $label) {
            $owner = self::themeOf($type);

            if ($owner === '') {
                $shared[$type] = $label;
            } elseif ($owner === $themeSlug) {
                $own[$type] = $label;
            }
        }

        $groups = [];

        if ($own !== [] && $theme !== null) {
            $groups[$theme::name()] = $own;
        }

        if ($shared !== []) {
            $groups['Fælles blokke'] = $shared;
        }

        return $groups;
    }
}
