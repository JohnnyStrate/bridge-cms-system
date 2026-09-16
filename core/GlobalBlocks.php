<?php
declare(strict_types=1);

/**
 * Blokke der hører til HELE sitet, ikke til én side.
 *
 * En "slot" er en fast plads i layoutet — fx toppen af hver side. Slot'en
 * bestemmer selv, hvilken bloktype der må ligge i den. Browseren kan altså
 * ikke sende en bloktype ind og få den gemt som global; den kan kun pege
 * på en slot, vi selv har defineret her.
 *
 * Placeringen ('before'/'after') afgør, om blokken lægges før eller efter
 * sidens egne blokke. Det er den eneste "rækkefølge", en global blok har —
 * derfor mangler der bevidst en sort_order i tabellen.
 *
 * Vil I have en global footer, er det én linje mere i SLOTS.
 */
final class GlobalBlocks
{
      /** @var array<string, array<string, string>> */
    public const SLOTS = [
        'header' => [
            'block_type' => 'navbar',
            'hint'       => 'vises på alle sider',
            'position'   => 'before',
        ],
        'footer' => [
            'block_type' => 'footer',
            'hint'       => 'vises på alle sider',
            'position'   => 'after',
        ],
    ];

    public function __construct(private readonly GlobalBlockRepository $repository)
    {
    }

    /** Bloktypen en slot må indeholde, eller null hvis slot'en ikke findes. */
    public static function typeFor(string $slot): ?string
    {
        return self::SLOTS[$slot]['block_type'] ?? null;
    }

    /**
     * Bloktyper der KUN kan være globale. De skjules i sidens "+"-menu,
     * så brugeren ikke kan lave en løs navbar på en enkelt side.
     *
     * @return array<int, string>
     */
    public static function managedTypes(): array
    {
        return array_column(self::SLOTS, 'block_type');
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

            // Slot'en bestemmer typen — ikke det, der tilfældigvis står
            // i kolonnen.
            $row['block_type'] = self::SLOTS[$slot]['block_type'];
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