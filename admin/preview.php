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

$html = PageRenderer::renderDocument(
    $page,
    $blocks,
    RenderContext::editor($basePath, SiteMap::fromPages($pageRepository->findAll()))
);

/*
 * Editorens egen bjælke oven på forhåndsvisningen.
 *
 * Den ligger IKKE i en blok og eksporteres derfor aldrig — den findes kun
 * her i preview.php. Stilen står inline, fordi det færdige dokument kun
 * henter base.css og blokkenes CSS, ikke admin.css.
 *
 * Forhåndsvisningen åbnes i en ny fane. Er fanen åbnet af editoren
 * (window.opener), lukker knappen den, så brugeren lander i den fane, der
 * stadig har det ugemte arbejde. Ellers navigerer linket som normalt.
 */
$bar = '<style>'
    . '.preview-bar{position:fixed;inset-inline:0;bottom:0;z-index:999;'
    . 'display:flex;align-items:center;justify-content:space-between;gap:1rem;'
    . 'padding:.6rem 1.25rem;background:#1f2933;color:#fff;'
    . 'font:14px/1.4 system-ui,sans-serif}'
    . '.preview-bar__label{opacity:.8}'
    . '.preview-bar__btn{display:inline-block;padding:.45rem 1.1rem;'
    . 'border-radius:6px;background:#e8a15d;color:#fff;text-decoration:none;'
    . 'font-weight:600}'
    . '.preview-bar__btn:hover{filter:brightness(1.1)}'
    . 'body{padding-bottom:3.5rem}'
    . '</style>'
    . '<div class="preview-bar">'
    . '<span class="preview-bar__label">Forhåndsvisning — dine ændringer er ikke gemt endnu</span>'
    . '<a class="preview-bar__btn" href="editor.php?page_id=' . (int) $page['id'] . '"'
    . ' onclick="if (window.opener) { window.close(); return false; }">'
    . '&larr; Tilbage til editoren</a>'
    . '</div>';

header('Content-Type: text/html; charset=utf-8');

// Indsættes før </body>, så dokumentet forbliver gyldigt.
echo str_replace('</body>', $bar . '</body>', $html);