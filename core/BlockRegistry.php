<?php
declare(strict_types=1);

/**
 * Oversætter en block_type-streng fra databasen til den PHP-klasse,
 * der kan tegne blokken.
 *
 * Det er systemets allowlist. En værdi fra databasen bliver ALDRIG brugt
 * til at bygge en filsti direkte — den slås op i kortet nedenfor, og
 * findes den ikke, sker der ingenting. Uden det trin ville en manipuleret
 * block_type kunne pege på en vilkårlig fil på serveren.
 *
 * At tilføje en ny bloktype er to trin: opret mappen under det tema den
 * hører til (fx /blocks/blaa-tema/), og tilføj én linje i kortet. Ingen
 * ændringer i renderer, editor eller database.
 *
 * NAVNGIVNING
 * Nøglen til venstre er databasenøglen. Den står i page_blocks.block_type på
 * alle eksisterende sider og må derfor ikke ændres uden en migration.
 *
 * Blokkene i det blå tema var de første i projektet og har af samme grund
 * korte navne ('hero', 'welcome'). Blokke i et NYT tema navngives med temaet
 * foran — 'tema1-hero', 'tema1-welcome' — så to temaer kan have hver sin
 * hero uden at støde sammen. Klassenavnet følger samme mønster
 * (Tema1HeroBlock), fordi PHP kun har ét navnerum her.
 */
final class BlockRegistry
{
    /** @var array<string, class-string<BlockInterface>> */
    private const BLOCKS = [
        // Fælles — bruges af alle temaer (blocks/faelles/)
        'navbar'   => NavbarBlock::class,
        'footer'   => FooterBlock::class,
        'image'    => ImageBlock::class,
        'textarea' => TextAreaBlock::class,
        'gallery'  => GalleryBlock::class,

        // Blåt tema (blocks/blaa-tema/)
        'hero'     => HeroBlock::class,
        'welcome'  => WelcomeBlock::class,
        'boxcards' => BoxCardsBlock::class,
        'quote'    => QuoteBlock::class,
        'textbox'  => TextBoxBlock::class,
        'ranklist' => RankListBlock::class,
        'table'    => TableBlock::class,

        // Tema 1 (blocks/tema1/)
        'tema1-navbar' => Tema1NavbarBlock::class,
    ];

    private function __construct()
    {
    }

    /**
     * @return class-string<BlockInterface>|null
     */
    public static function get(string $type): ?string
    {
        $class = self::BLOCKS[$type] ?? null;

        // Klassen indlæses af autoloaderen på dette tidspunkt.
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

    /**
     * Alle bloktyper med deres visningsnavn. Bruges til "+"-menuen i
     * editoren, så listen dér altid matcher, hvad systemet rent faktisk
     * kan tegne.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $result = [];

        foreach (array_keys(self::BLOCKS) as $type) {
            $class = self::get($type);

            if ($class !== null) {
                $result[$type] = $class::label();
            }
        }

        return $result;
    }

    /**
     * Det samme som all(), men delt op i temaer.
     *
     * Editorens "+"-menu bruger denne, for når to temaer hver har en hero,
     * står der ellers "Hero" to gange i listen uden at brugeren kan se
     * forskel. Temaet læses af blokkens egen mappe, så listen ikke kan komme
     * til at sige noget andet end filerne.
     *
     * @return array<string, array<string, string>> temanavn => (type => navn)
     */
    public static function grouped(): array
    {
        $labels  = self::all();
        $folders = [];

        foreach (array_keys($labels) as $type) {
            $class = self::get($type);

            $folders[$type] = $class === null
                ? ''
                : Themes::ofClass($class, 'blocks');
        }

        return Themes::group($labels, $folders);
    }
}