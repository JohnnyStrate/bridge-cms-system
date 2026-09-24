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
 *   template-preview.php?template=klubforside
 */

require_once __DIR__ . '/../bootstrap.php';

$slug = trim((string) filter_input(INPUT_GET, 'template'));

$pdo       = Database::getConnection();


// Slug'en slås op blandt de skabeloner, der faktisk findes i
// /templates/. En gættet værdi rammer derfor ingenting.
$template = TemplateRegistry::get($slug);

if ($template === null) {
    http_response_code(404);
    exit('Skabelonen blev ikke fundet.');
}

// Sitets navbar og footer lægges omkring, så brugeren ser skabelonen,
// som den kommer til at stå på sitet — ikke som løsrevne sektioner.
$globals = new GlobalBlocks(new GlobalBlockRepository($pdo));
// Skabelonens blokke tegnes direkte fra koden. Værdierne valideres af
// PageRenderer mod blokkenes skemaer, præcis som når siden er oprettet.
$blocks = $globals->wrap(array_map(
    static fn (array $block): array => [
        'block_type' => (string) ($block['type'] ?? ''),
        'settings'   => is_array($block['settings'] ?? null) ? $block['settings'] : [],
        'styles'     => is_array($block['styles'] ?? null) ? $block['styles'] : [],
    ],
    $template::blocks()
));

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$context  = RenderContext::editor(
    $basePath,
    SiteMap::fromPages((new PageRepository($pdo))->findAll())
);

// renderDocument() forventer en side. Skabelonen har ingen, så vi giver
// den det eneste, den bruger: en titel.
$html = PageRenderer::renderDocument(
    ['title' => 'Skabelon: ' . $template::name()],
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