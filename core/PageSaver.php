<?php
declare(strict_types=1);

/**
 * Gemmer en hel side i ét kald.
 *
 * Editoren holder sidens tilstand i browseren og sender den samlet, når
 * brugeren trykker Gem. Denne klasse tager imod og skriver den til
 * databasen — ændrede blokke, nye blokke og slettede blokke på én gang.
 *
 * GLOBALE BLOKKE
 * Editoren sender også de globale blokke med i samme kald. De hører ikke
 * til siden, men til sitet, og gemmes derfor i global_blocks. Samme
 * transaktion: en navbar må ikke blive gemt, hvis resten af siden fejler.
 *
 * ALT ELLER INTET
 * Det hele sker i én transaktion. Uden den kunne fem ud af otte blokke
 * blive gemt, hvorefter brugeren står med en side, der hverken er den
 * gamle eller den nye — og ikke har nogen måde at komme tilbage på.
 *
 * TILLID
 * Serveren stoler ikke på det, browseren sender, selv om det er
 * brugerens egen maskine. Bloktyper slås op i registryet, felter
 * filtreres gennem blokkens skema, og værdier valideres. Det, der ikke
 * passer, ryger ud i stedet for at blive skrevet ned.
 */
final class PageSaver
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly PageRepository $pages,
        private readonly BlockRepository $blocks,
        private readonly ?GlobalBlockRepository $globals = null
    ) {
    }

    /**
     * @param array<string, mixed> $payload Afkodet JSON fra editoren.
     * @return array{blocks: int, deleted: int, globals: int}
     *
     * @throws InvalidArgumentException ved ugyldigt input fra brugeren.
     */
    public function save(int $pageId, array $payload): array
    {
        $page = $this->pages->find($pageId);

        if ($page === null) {
            throw new InvalidArgumentException('Siden findes ikke.');
        }

        $pageData     = is_array($payload['page'] ?? null) ? $payload['page'] : [];
        $incoming     = is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [];
        $existingIds  = $this->blocks->idsForPage($pageId);
        $keptIds      = [];
        $savedGlobals = 0;

        $this->pdo->beginTransaction();

        try {
            $this->savePageSettings($pageId, $page, $pageData);

            foreach (array_values($incoming) as $position => $block) {
                if (!is_array($block)) {
                    continue;
                }

                $saved = $this->saveBlock($pageId, $block, ($position + 1) * 10, $existingIds);

                if ($saved !== null) {
                    $keptIds[] = $saved;
                }
            }

            // Blokke der findes i databasen, men ikke i det browseren
            // sendte, er dem brugeren har slettet i editoren.
            $removed = array_values(array_diff($existingIds, $keptIds));
            $this->blocks->deleteMany($pageId, $removed);

            // Mangler nøglen helt, er det en ældre klient. Så rører vi
            // ikke de globale blokke — frem for at slette dem alle sammen.
            if ($this->globals !== null && array_key_exists('globals', $payload)) {
                $savedGlobals = $this->saveGlobals(
                    is_array($payload['globals']) ? $payload['globals'] : []
                );
            }

            $this->pdo->commit();

            return [
                'blocks'  => count($keptIds),
                'deleted' => count($removed),
                'globals' => $savedGlobals,
            ];

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Gemmer sitets globale blokke.
     *
     * Browseren sender kun en slot og nogle værdier. HVILKEN bloktype
     * slot'en indeholder, slås op i GlobalBlocks — ellers kunne et
     * manipuleret kald gøre en vilkårlig blok global.
     *
     * En slot, der ikke er med i det browseren sendte, er en blok
     * brugeren har fjernet. Samme logik som for sidens egne blokke.
     *
     * @param array<int, mixed> $incoming
     */
    private function saveGlobals(array $incoming): int
    {
        $kept = [];

        foreach ($incoming as $item) {
            if (!is_array($item)) {
                continue;
            }

            $slot = (string) ($item['slot'] ?? '');
            $type = GlobalBlocks::typeFor($slot);

            if ($type === null) {
                continue;
            }

            $class = BlockRegistry::get($type);

            if ($class === null) {
                continue;
            }

            $settings = FieldValidator::validateAll(
                $class::getSchema(),
                is_array($item['settings'] ?? null) ? $item['settings'] : []
            );

            $styles = FieldValidator::validateAll(
                $class::getStyleSchema(),
                is_array($item['styles'] ?? null) ? $item['styles'] : []
            );

            $this->globals->save($slot, $type, $settings, $styles);
            $kept[] = $slot;
        }

        foreach (array_keys(GlobalBlocks::SLOTS) as $slot) {
            if (!in_array($slot, $kept, true)) {
                $this->globals->delete($slot);
            }
        }

        return count($kept);
    }

    /**
     * @param array<string, mixed> $current
     * @param array<string, mixed> $input
     */
    private function savePageSettings(int $pageId, array $current, array $input): void
    {
        $title  = trim((string) ($input['title'] ?? $current['title']));
        $slug   = trim((string) ($input['slug'] ?? $current['slug']));
        $status = (string) ($input['status'] ?? $current['status']);

        if ($title === '') {
            throw new InvalidArgumentException('Siden skal have en titel.');
        }

        if (!Slug::isValid($slug)) {
            throw new InvalidArgumentException(
                'Ugyldig webadresse. Brug kun små bogstaver, tal og bindestreg.'
            );
        }

        // ENUM'en i databasen ville afvise andet, men en tydelig besked
        // her er bedre end en rå databasefejl.
        if (!in_array($status, ['draft', 'published'], true)) {
            throw new InvalidArgumentException('Ugyldig status.');
        }

        $parentId = $this->resolveParent($pageId, $current, $input);

        // Sluggen må ikke kollidere under den forælder, siden ender under
        // — ikke den, den kom fra. Flytter man en side, kan den støde ind
        // i en anden slug på det nye niveau.
        $clash = $this->pages->findBySlug($slug, $parentId);

        if ($clash !== null && (int) $clash['id'] !== $pageId) {
            throw new InvalidArgumentException(
                "Webadressen '{$slug}' er allerede i brug af en anden side på samme niveau."
            );
        }

        $this->pages->update($pageId, $title, $slug, $status, $parentId);
    }

    /**
     * Afgør, hvilken forælder siden skal have, og afviser flyt der ville
     * ødelægge hierarkiet.
     *
     * @param array<string, mixed> $current
     * @param array<string, mixed> $input
     */
    private function resolveParent(int $pageId, array $current, array $input): ?int
    {
        if (!array_key_exists('parent_id', $input)) {
            return $current['parent_id'] !== null ? (int) $current['parent_id'] : null;
        }

        $parentId = (int) $input['parent_id'];

        // 0 fra dropdownen betyder "ingen forælder" — siden ligger i roden.
        if ($parentId <= 0) {
            return null;
        }

        if ($parentId === $pageId) {
            throw new InvalidArgumentException('En side kan ikke være sin egen forælder.');
        }

        if ($this->pages->find($parentId) === null) {
            throw new InvalidArgumentException('Den valgte forælder findes ikke.');
        }

        // Uden denne kontrol kunne en side flyttes ned under sit eget
        // barnebarn. Forældrekæden ville blive cyklisk, og hverken
        // eksporten eller sidetræet kunne finde en vej ud af den.
        if (in_array($parentId, $this->pages->descendantIds($pageId), true)) {
            throw new InvalidArgumentException(
                'Siden kan ikke placeres under en af sine egne undersider.'
            );
        }

        return $parentId;
    }

    /**
     * Gemmer én blok. Returnerer blokkens id, eller null hvis den blev
     * afvist.
     *
     * @param array<string, mixed> $block
     * @param array<int, int>      $existingIds
     */
    private function saveBlock(
        int $pageId,
        array $block,
        int $sortOrder,
        array $existingIds
    ): ?int {
        $type  = (string) ($block['type'] ?? '');
        $class = BlockRegistry::get($type);

        // Ukendt bloktype afvises. Det er allowlisten, der forhindrer,
        // at browseren kan opfinde en bloktype.
        if ($class === null) {
            return null;
        }

        // En global bloktype hører ikke til på en enkelt side. Kom den
        // alligevel med i blocks-listen, er det en fejl i klienten —
        // ikke noget vi skriver ned.
        if (GlobalBlocks::isManaged($type)) {
            return null;
        }

        // Kun felter, skemaet kender, kommer med — og hver værdi tjekkes
        // mod sin felttype. Det er her et forsøg på at gemme
        // 'red; background:url(evil)' som farve bliver til standardværdien.
        $settings = FieldValidator::validateAll(
            $class::getSchema(),
            is_array($block['settings'] ?? null) ? $block['settings'] : []
        );

        $styles = FieldValidator::validateAll(
            $class::getStyleSchema(),
            is_array($block['styles'] ?? null) ? $block['styles'] : []
        );

        $id = isset($block['id']) ? (int) $block['id'] : 0;

        // Et id, der ikke i forvejen hører til denne side, behandles som
        // en ny blok. Så kan et manipuleret id ikke overskrive en blok
        // på en anden side.
        if ($id > 0 && in_array($id, $existingIds, true)) {
            $this->blocks->update($id, $pageId, $settings, $styles, $sortOrder);
            return $id;
        }

        return $this->blocks->insert($pageId, $type, $settings, $styles, $sortOrder);
    }
}