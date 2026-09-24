<?php
declare(strict_types=1);

/**
 * Blokke der hører til HELE sitet, ikke til én side.
 *
 * En "slot" er en fast plads i layoutet — fx toppen af hver side. Slot'en
 * bestemmer selv, hvilke bloktyper der må ligge i den. Browseren kan altså
 * ikke sende en vilkårlig bloktype ind og få den gemt som global; den kan
 * kun pege på en slot og vælge blandt de typer, vi selv har skrevet her.
 *
 * FLERE TYPER PR. SLOT
 * 'block_types' er en LISTE, fordi hvert tema kan have sin egen navbar. Der
 * er stadig kun ÉN navbar på sitet ad gangen — listen siger, hvad man må
 * vælge imellem, ikke hvor mange der vises. Den første i listen er
 * standarden og bruges, hvis det gemte valg ikke længere findes.
 *
 * Placeringen ('before'/'after') afgør, om blokken lægges før eller efter
 * sidens egne blokke. Det er den eneste "rækkefølge", en global blok har —
 * derfor mangler der bevidst en sort_order i tabellen.
 *
 * Vil I have en ny global plads, er det én blok mere i SLOTS.
 */
final class GlobalBlocks
{
    /** @var array<string, array<string, mixed>> */
    public const SLOTS = [
        'header' => [
            // Første type er standarden. Tilføj temaets egen navbar her.
            'block_types' => ['navbar', 'tema1-navbar'],
            'hint'        => 'vises på alle sider',
            'position'    => 'before',
        ],
        'footer' => [
            'block_types' => ['footer'],
            'hint'        => 'vises på alle sider',
            'position'    => 'after',
        ],
    ];

    public function __construct(private readonly GlobalBlockRepository $repository)
    {
    }

    /**
     * De bloktyper en slot må indeholde. Tom liste = slot'en findes ikke.
     *
     * Typer, der ikke findes i BlockRegistry, sorteres fra. Så kan en
     * halvfærdig eller slettet blok ikke give en tom knap i editoren.
     *
     * @return array<int, string>
     */
    public static function typesFor(string $slot): array
    {
        $types = (array) (self::SLOTS[$slot]['block_types'] ?? []);

        return array_values(array_filter($types, static function ($type): bool {
            return is_string($type) && BlockRegistry::exists($type);
        }));
    }

    /**
     * Den bloktype en slot skal gemmes med.
     *
     * $requested er brugerens valg, som det kom fra browseren. Det bruges
     * KUN, hvis det står i slot'ens egen liste — ellers falder vi tilbage
     * til standarden. Det er dét trin, der gør, at et manipuleret kald
     * ikke kan gøre en vilkårlig blok global.
     */
    public static function typeFor(string $slot, string $requested = ''): ?string
    {
        $types = self::typesFor($slot);

        if ($types === []) {
            return null;
        }

        return in_array($requested, $types, true) ? $requested : $types[0];
    }

    /**
     * Bloktyper der KUN kan være globale. De skjules i sidens "+"-menu,
     * så brugeren ikke kan lave en løs navbar på en enkelt side.
     *
     * @return array<int, string>
     */
    public static function managedTypes(): array
    {
        $types = [];

        foreach (array_keys(self::SLOTS) as $slot) {
            foreach (self::typesFor($slot) as $type) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public static function isManaged(string $blockType): bool
    {
        return in_array($blockType, self::managedTypes(), true);
    }

    /**
     * De slots der faktisk er oprettet, klar til rendering.
     *
     * @return array<string, array<string, mixed>> slot => blokrække
     */
    public function saved(): array
    {
        $rows = [];

        foreach ($this->repository->all() as $row) {
            $slot = (string) $row['slot'];

            // En slot der er fjernet fra koden, men stadig står i
            // databasen, springes over frem for at vælte siden.
            if (!isset(self::SLOTS[$slot])) {
                continue;
            }

            // Slot'en har det sidste ord: står der en type i kolonnen, som
            // slot'en ikke tillader længere (fx et tema, der er fjernet),
            // bruges slot'ens standard i stedet for at vise ingenting.
            $type = self::typeFor($slot, (string) $row['block_type']);

            if ($type === null) {
                continue;
            }

            $row['block_type'] = $type;
            $rows[$slot]       = $row;
        }

        return $rows;
    }

    /**
     * Lægger de globale blokke omkring sidens egne.
     *
     * Resultatet er én almindelig blokliste, som PageRenderer kan tegne
     * uden at vide, at nogle af blokkene kom et andet sted fra. Derfor
     * kommer både CSS og billeder automatisk med i eksporten.
     *
     * @param array<int, array<string, mixed>> $pageBlocks
     * @return array<int, array<string, mixed>>
     */
    public function wrap(array $pageBlocks, bool $onlyVisible = true): array
    {
        $before = [];
        $after  = [];

        foreach ($this->saved() as $slot => $row) {
            if ($onlyVisible && !$row['is_visible']) {
                continue;
            }

            if ((self::SLOTS[$slot]['position'] ?? 'before') === 'after') {
                $after[] = $row;
            } else {
                $before[] = $row;
            }
        }

        return array_merge($before, $pageBlocks, $after);
    }
}