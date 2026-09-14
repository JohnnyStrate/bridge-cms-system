<?php
declare(strict_types=1);

/**
 * Forhåndsvisning af ugemt arbejde.
 *
 * Editoren sender sidens nuværende tilstand med — også de globale blokke
 * — og siden tegnes præcis som den ville se ud på det færdige website.
 * Der skrives intet til databasen.
 */

require_once __DIR__ . '/../bootstrap.php';

$pageId = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT) ?: 0;

$pdo            = Database::getConnection();
$pageRepository = new PageRepository($pdo);
$page           = $pageRepository->find($pageId);

if ($page === null) {
    http_response_code(404);
    exit('Siden blev ikke fundet.');
}

$state = json_decode((string) ($_POST['state'] ?? ''), true);

if (!is_array($state)) {
    http_response_code(400);
    exit('Ingen data at forhåndsvise.');
}

// Titlen kan være ændret i editoren uden at være gemt endnu.
if (isset($state['page']['title'])) {
    $page['title'] = (string) $state['page']['title'];
}

/**
 * Editorens format oversættes til det, rendereren forventer. Værdierne
 * valideres på samme måde som ved gemning, så forhåndsvisningen viser
 * nøjagtigt det, der ville blive gemt.
 */
$toBlock = static function (string $type, array $incoming): ?array {
    $class = BlockRegistry::get($type);

    if ($class === null) {
        return null;
    }

    return [
        'block_type' => $type,
        'settings'   => FieldValidator::validateAll(
            $class::getSchema(),
            is_array($incoming['settings'] ?? null) ? $incoming['settings'] : []
        ),
        'styles'     => FieldValidator::validateAll(
            $class::getStyleSchema(),
            is_array($incoming['styles'] ?? null) ? $incoming['styles'] : []
        ),
    ];
};

$blocks = [];

foreach ((array) ($state['blocks'] ?? []) as $incoming) {
    if (!is_array($incoming)) {
        continue;
    }

    $block = $toBlock((string) ($incoming['type'] ?? ''), $incoming);

    if ($block !== null) {
        $blocks[] = $block;
    }
}

// De globale blokke vises også ugemt, så brugeren kan se en ændret
// navbar, før den slår igennem på hele sitet.
$before = [];
$after  = [];

foreach ((array) ($state['globals'] ?? []) as $incoming) {
    if (!is_array($incoming)) {
        continue;
    }

    $slot = (string) ($incoming['slot'] ?? '');
    $type = GlobalBlocks::typeFor($slot);

    if ($type === null) {
        continue;
    }

    $block = $toBlock($type, $incoming);

    if ($block === null) {
        continue;
    }

    if ((GlobalBlocks::SLOTS[$slot]['position'] ?? 'before') === 'after') {
        $after[] = $block;
    } else {
        $before[] = $block;
    }
}

$blocks = array_merge($before, $blocks, $after);

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

header('Content-Type: text/html; charset=utf-8');

echo PageRenderer::renderDocument(
    $page,
    $blocks,
    RenderContext::editor($basePath, SiteMap::fromPages($pageRepository->findAll()))
);