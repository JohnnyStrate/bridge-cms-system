<?php
declare(strict_types=1);

/**
 * Forhåndsvisning af en skabelon, FØR der er oprettet en side ud fra den.
 *
 * Vises i en iframe inde i "Opret side". Iframen giver skabelonen sit
 * eget dokument, så blokkenes CSS ikke blander sig med adminpanelets —
 * og omvendt. Det er samme grund til, at preview.php er en hel side.
 *
 * Der skrives intet til databasen. Skabelonens blokke hentes og tegnes
 * direkte, præcis som PageBuilder ville kopiere dem ned på en ny side.
 * PageRenderer validerer værdierne mod blokkenes nuværende skema, så
 * forhåndsvisningen viser det, brugeren faktisk ville få.
 *
 *   template-preview.php?template_id=1
 */

require_once __DIR__ . '/../bootstrap.php';

$templateId = filter_input(INPUT_GET, 'template_id', FILTER_VALIDATE_INT) ?: 0;

$pdo       = Database::getConnection();
$templates = new TemplateRepository($pdo);

// find() returnerer kun aktive skabeloner, så en deaktiveret skabelon
// kan heller ikke forhåndsvises ved at gætte dens id.
$template = $templates->find($templateId);

if ($template === null) {
    http_response_code(404);
    exit('Skabelonen blev ikke fundet.');
}

// Sitets navbar og footer lægges omkring, så brugeren ser skabelonen,
// som den kommer til at stå på sitet — ikke som løsrevne sektioner.
$globals = new GlobalBlocks(new GlobalBlockRepository($pdo));
$blocks  = $globals->wrap($templates->findBlocks($templateId));

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$context  = RenderContext::editor(
    $basePath,
    SiteMap::fromPages((new PageRepository($pdo))->findAll())
);

// renderDocument() forventer en side. Skabelonen har ingen, så vi giver
// den det eneste, den bruger: en titel.
$html = PageRenderer::renderDocument(
    ['title' => 'Skabelon: ' . (string) $template['name']],
    $blocks,
    $context
);

/*
 * Links slås fra i forhåndsvisningen. Et klik på et menupunkt ville
 * ellers navigere iframen væk fra skabelonen og efterlade brugeren på
 * en helt anden side inde i boksen.
 *
 * Stilen står her og ikke i en blok, så den aldrig kommer med i eksporten.
 */
$previewStyle = '<style>a{pointer-events:none;cursor:default}</style>';

header('Content-Type: text/html; charset=utf-8');

echo str_replace('</head>', $previewStyle . '</head>', $html);