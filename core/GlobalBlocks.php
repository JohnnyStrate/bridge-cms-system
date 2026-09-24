<?php
declare(strict_types=1);

/**
 * Blokke der hører til HELE sitet, ikke til én side: navbar og footer.
 *
 * En "slot" er en fast plads i layoutet — fx toppen af hver side. HVILKEN
 * blok der ligger i slot'en, bestemmer temaet selv i sin globals():
 *
 *     Tema1Theme::globals() = ['header' => 'tema1-navbar', 'footer' => 'tema1-footer']
 *
 * Browseren kan altså ikke vælge bloktypen. Den kan kun pege på en slot,
 * og serveren slår typen op i det aktive tema.
 *
 * ÉN PR. TEMA
 * Hvert tema har sin egen navbar og footer i global_blocks, og kun det
 * aktive temas vises. Skifter man tema og tilbage igen, er ens gamle
 * navbar der stadig.
 *
 * TRE TILSTANDE PR. SLOT
 *   - Ingen række:        temaets dummy-indhold (blokkens standardværdier).
 *                         Sådan ser et nyvalgt tema ud med det samme.
 *   - Række, synlig:      det, brugeren har gemt.
 *   - Række, is_visible=0: brugeren har fjernet den i editoren.
 *
 * Placeringen ('before'/'after') afgør, om blokken lægges før eller efter
 * sidens egne blokke. Vil I have en ny global plads, er det én linje mere
 * i SLOTS og en linje i de temaers globals(), der skal bruge den.
 */
final class GlobalBlocks
{
    /** @var array<string, array{label: string, hint: string, position: string}> */
    public const SLOTS = [
        'header' => [
            'label'    => 'Navbar',
            'hint'     => 'vises på alle sider',
            'position' => 'before',
        ],
        'footer' => [
            'label'    => 'Footer',
            'hint'     => 'vises på alle sider',
            'position' => 'after',
        ],
    ];

    /** @var array<string, array<string, mixed>>|null */
    private ?array $rows = null;

    public function __construct(
        private readonly GlobalBlockRepository $repository,
        private readonly string $theme
    ) {
    }

    /** Temaets slug, fx 'tema1'. */
    public function theme(): string
    {
        return $this->theme;
    }

    /**
     * Bloktypen et tema bruger i en slot. null = temaet har intet i den
     * slot, eller blokken findes ikke (endnu).
     */
    public static function typeFor(string $slot, string $theme): ?string
    {
        $class = ThemeRegistry::get($theme);

        if ($class === null || !isset(self::SLOTS[$slot])) {
            return null;
        }

        $type = (string) ($class::globals()[$slot] ?? '');

        return BlockRegistry::exists($type) ? $type : null;
    }

    /** Bloktypen i en slot for DETTE tema. */
    public function type(string $slot): ?string
    {
        return self::typeFor($slot, $this->theme);
    }

    /**
     * Bloktyper der KUN kan være globale — på tværs af alle temaer. De
     * skjules i sidens "+"-menu og afvises, hvis de sendes som sideblok.
     *
     * @return array<int, string>
     */
    public static function managedTypes(): array
    {
        $types = [];

        foreach (ThemeRegistry::all() as $theme) {
            foreach ($theme::globals() as $type) {
                $types[] = (string) $type;
            }
        }

        return array_values(array_unique($types));
    }

    public static function isManaged(string $blockType): bool
    {
        return in_array($blockType, self::managedTypes(), true);
    }

    /**
     * Temaets globale blokke, klar til rendering — også de skjulte.
     *
     * Værdierne er valideret mod blokkens skema. Har temaet skiftet
     * bloktype i en slot, siden rækken blev gemt, bruges temaets nye type,
     * og de felter, der passer, beholdes.
     *
     * @return array<string, array<string, mixed>> slot => blokrække
     */
    public function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        $saved = [];

        foreach ($this->repository->forTheme($this->theme) as $row) {
            $saved[(string) $row['slot']] = $row;
        }

        $rows = [];

        foreach (array_keys(self::SLOTS) as $slot) {
            $type  = $this->type($slot);
            $class = $type === null ? null : BlockRegistry::get($type);

            if ($class === null) {
                continue;
            }

            $row = $saved[$slot] ?? null;

            $rows[$slot] = [
                'slot'       => $slot,
                'block_type' => $type,
                'settings'   => $row === null
                    ? $class::defaultSettings()
                    : FieldValidator::validateAll($class::getSchema(), $row['settings']),
                'styles'     => $row === null
                    ? $class::defaultStyles()
                    : FieldValidator::validateAll($class::getStyleSchema(), $row['styles']),
                'is_visible' => $row === null ? true : (bool) $row['is_visible'],
            ];
        }

        return $this->rows = $rows;
    }

    /**
     * Kun de synlige. Det er dem, editoren og siden viser.
     *
     * @return array<string, array<string, mixed>>
     */
    public function visible(): array
    {
        return array_filter(
            $this->rows(),
            static fn (array $row): bool => (bool) $row['is_visible']
        );
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
    public function wrap(array $pageBlocks): array
    {
        $before = [];
        $after  = [];

        foreach ($this->visible() as $slot => $row) {
            if (self::SLOTS[$slot]['position'] === 'after') {
                $after[] = $row;
            } else {
                $before[] = $row;
            }
        }

        return array_merge($before, $pageBlocks, $after);
    }

    /**
     * Gemmer det, editoren sendte. Slots, der ikke er med, er dem
     * brugeren har fjernet — de skjules, men indholdet bevares.
     *
     * Bloktypen kommer fra temaet, ikke fra browseren.
     *
     * @param array<int, mixed> $incoming
     * @return int Antal gemte slots.
     */
    public function saveFromEditor(array $incoming): int
    {
        $kept = [];

        foreach ($incoming as $item) {
            if (!is_array($item)) {
                continue;
            }

            $slot  = (string) ($item['slot'] ?? '');
            $type  = $this->type($slot);
            $class = $type === null ? null : BlockRegistry::get($type);

            if ($class === null || in_array($slot, $kept, true)) {
                continue;
            }

            $this->repository->save(
                $this->theme,
                $slot,
                $type,
                FieldValidator::validateAll(
                    $class::getSchema(),
                    is_array($item['settings'] ?? null) ? $item['settings'] : []
                ),
                FieldValidator::validateAll(
                    $class::getStyleSchema(),
                    is_array($item['styles'] ?? null) ? $item['styles'] : []
                )
            );

            $kept[] = $slot;
        }

        foreach ($this->rows() as $slot => $row) {
            if (!in_array($slot, $kept, true)) {
                $this->repository->hide(
                    $this->theme,
                    $slot,
                    (string) $row['block_type'],
                    $row['settings'],
                    $row['styles']
                );
            }
        }

        $this->rows = null;

        return count($kept);
    }
}
