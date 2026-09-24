<?php
declare(strict_types=1);

/**
 * Temaerne i projektet.
 *
 * Et tema er ganske enkelt en mappe. Den samme mappenavn bruges to steder,
 * så de to halvdele af et tema ligger side om side:
 *
 *     blocks/blaa-tema/hero/        blokkene
 *     templates/blaa-tema/          skabelonerne, der sætter dem sammen
 *
 * Denne klasse kender KUN mappenavnenes visningsnavne og rækkefølge. Hvilke
 * blokke og skabeloner der findes, bestemmes stadig af BlockRegistry og
 * TemplateRegistry — her står intet indhold, kun etiketter til editoren.
 *
 * SÅDAN TILFØJER DU ET TEMA
 *   1. Opret blocks/dit-tema/ og templates/dit-tema/.
 *   2. Skriv én linje i LABELS nedenfor.
 * Glemmer du trin 2, går intet i stykken: mappenavnet bruges som overskrift,
 * indtil nogen giver temaet et pænere navn.
 *
 * Mappenavne holdes med små bogstaver og uden æ, ø og å. Navnet ender som en
 * del af en URL (blocks/blaa-tema/hero/block.css), og den slags tegn giver
 * problemer i browseren og mellem Windows og Linux.
 */
final class Themes
{
    /**
     * Mappenavn => overskrift i editoren, i den rækkefølge de vises.
     *
     * "Fælles" står bevidst sidst: det er værktøjsblokke, man griber efter
     * bagefter, ikke dem man bygger en side op af.
     *
     * @var array<string, string>
     */
    private const LABELS = [
        'blaa-tema' => 'Blåt tema',
        'tema1'     => 'Tema 1',
        'faelles'   => 'Fælles blokke',
    ];

    private function __construct()
    {
    }

    /** Visningsnavnet for en temamappe. Ukendte mapper viser deres eget navn. */
    public static function label(string $folder): string
    {
        if ($folder === '') {
            // En blok, der ligger løst i /blocks/ uden temamappe. Den skal
            // stadig kunne vælges, så den får en overskrift frem for ingen.
            return 'Øvrige';
        }

        return self::LABELS[$folder] ?? $folder;
    }

    /**
     * Hvor et tema står i rækken. Ukendte temaer havner efter de kendte,
     * i stedet for at snige sig ind foran.
     */
    public static function position(string $folder): int
    {
        $order = array_keys(self::LABELS);
        $index = array_search($folder, $order, true);

        return $index === false ? count($order) : (int) $index;
    }

    /**
     * Temamappen en klasse ligger i, fundet ud fra klassens egen fil.
     *
     * Bruges af BlockRegistry og TemplateRegistry, så en blok ikke skal
     * fortælle om sit tema i kode — mappen ER svaret, og så kan de to ikke
     * komme til at sige noget forskelligt.
     *
     * Begge layout understøttes:
     *     blocks/blaa-tema/hero/Hero.php      → 'blaa-tema'
     *     templates/blaa-tema/Klub.php        → 'blaa-tema'
     *
     * @param class-string $class
     */
    public static function ofClass(string $class, string $rootFolder): string
    {
        try {
            $file = (new ReflectionClass($class))->getFileName();
        } catch (ReflectionException) {
            return '';
        }

        if ($file === false) {
            return '';
        }

        $root     = str_replace('\\', '/', APP_ROOT . '/' . $rootFolder . '/');
        $absolute = str_replace('\\', '/', $file);

        if (!str_starts_with($absolute, $root)) {
            return '';
        }

        // Første mappeled efter blocks/ eller templates/ er temaet.
        $relative = substr($absolute, strlen($root));
        $segments = explode('/', $relative);

        return count($segments) >= 2 ? $segments[0] : '';
    }

    /**
     * Grupperer en liste efter tema og sorterer grupperne.
     *
     * @param array<string, string> $labels      nøgle => visningsnavn
     * @param array<string, string> $themeFolder nøgle => temamappe
     * @return array<string, array<string, string>> temanavn => (nøgle => navn)
     */
    public static function group(array $labels, array $themeFolder): array
    {
        $folders = array_unique(array_values($themeFolder));

        usort($folders, static function (string $a, string $b): int {
            return [self::position($a), self::label($a)]
               <=> [self::position($b), self::label($b)];
        });

        $grouped = [];

        foreach ($folders as $folder) {
            $group = [];

            foreach ($labels as $key => $label) {
                if (($themeFolder[$key] ?? '') === $folder) {
                    $group[$key] = $label;
                }
            }

            if ($group !== []) {
                $grouped[self::label($folder)] = $group;
            }
        }

        return $grouped;
    }
}
