<?php
declare(strict_types=1);

/**
 * "Dine sider" — oversigten over alle sider.
 *
 * Hver hovedside og dens undersider ligger i én .page-group. Det er
 * gruppen, der trækkes rundt i listen, så undersider altid flytter med
 * deres hovedside. Rækkefølgen gemmes af admin.js via reorder-pages.php.
 */

require_once __DIR__ . '/../bootstrap.php';

$pdo = Database::getConnection();

// Sider vises i træets rækkefølge med undersider under deres forælder,
// frem for som en flad liste hvor sammenhængen ikke kan ses.
$pages = PageTree::flatten((new PageRepository($pdo))->findAll());

// Listen deles op i grupper: én hovedside plus alle dens undersider
// (også undersider af undersider). Hver gruppe bliver ét element i
// listen, så træk-og-slip flytter hele familien på én gang og ikke
// kun den række, man tog fat i.
//
// flatten() returnerer allerede siderne i træets rækkefølge, så en ny
// gruppe starter præcis der, hvor dybden er 0.
$groups = [];
foreach ($pages as $page) {
    if ((int) $page['depth'] === 0 || $groups === []) {
        $groups[] = [];
    }
    $groups[array_key_last($groups)][] = $page;
}

// Nummeret i højre side tæller alle rækker, på tværs af grupperne.
$rowNumber = 0;

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dine sider</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css?v=3">
</head>
<body class="admin">

<nav class="sidebar">
    <p class="sidebar__brand">Adminpanel</p>
    <ul class="sidebar__nav">
        <li><a href="index.php" aria-current="page">Dine sider</a></li>
        <li><a href="create-page.php">Opret side</a></li>
        <li><a href="export.php">Udgiv</a></li>
<li><a href="galleries.php">Galleri</a></li>
        <li><a href="#">Indstillinger</a></li>
    </ul>
</nav>

<main class="content">
    <h1 class="content__title">Dine sider</h1>

    <?php /* Fejl fra delete-page.php, fx naar en side har undersider. */ ?>
    <?php if (isset($_GET['fejl'])): ?>
        <p class="alert" role="alert"><?= e((string) $_GET['fejl']) ?></p>
    <?php endif; ?>

    <ul class="page-list" id="page-list">
        <?php foreach ($groups as $group): ?>
            <?php $root = $group[0]; ?>
            <!--
                Én gruppe = én hovedside med dens undersider. Det er
                gruppen, der trækkes rundt, så undersiderne altid følger
                med deres hovedside.
            -->
            <li class="page-group<?= count($group) > 1 ? ' has-children' : '' ?>"
                data-group-id="<?= (int) $root['id'] ?>">
            <?php foreach ($group as $page): ?>
            <?php $rowNumber++; ?>
            <div class="page-row" data-page-id="<?= (int) $page['id'] ?>"
                data-depth="<?= (int) $page['depth'] ?>"
                style="--depth: <?= (int) $page['depth'] ?>">
                <?php /*
                    Grebet vises kun for hovedsider. Undersider flytter
                    sig sammen med deres hovedside, så de har et låst
                    greb, der blot viser tilhørsforholdet.
                */ ?>
                <span class="page-row__handle<?= $page['depth'] > 0 ? ' is-locked' : '' ?>"
                      aria-hidden="true"><?= $page['depth'] > 0 ? '└' : '⠿' ?></span>

                <span class="page-row__title"><?= e($page['title']) ?></span>

                <span class="badge badge--<?= e($page['status']) ?>">
                    <?= $page['status'] === 'published' ? 'udgivet' : 'kladde' ?>
                </span>

                <a class="icon-btn icon-btn--view"
                   href="<?= e($basePath) ?>/page.php?id=<?= (int) $page['id'] ?>"
                   target="_blank" rel="noopener"
                   title="Se siden">&#128065;</a>

                <a class="icon-btn icon-btn--edit"
                   href="editor.php?page_id=<?= (int) $page['id'] ?>"
                   title="Rediger">&#9998;</a>

                <!--
                    Sletning sker via POST, ikke via et link. Et link kan
                    følges af browserens forudindlæsning eller en
                    historik-knap, og så er siden væk uden at nogen
                    trykkede på noget.
                -->
                <form method="post" action="delete-page.php" class="page-row__delete"
                      onsubmit="return confirm('Slet siden &quot;<?= e($page['title']) ?>&quot;? Det kan ikke fortrydes.');">
                    <input type="hidden" name="page_id" value="<?= (int) $page['id'] ?>">
                    <button type="submit" class="icon-btn icon-btn--delete"
                            title="Slet">&#128465;</button>
                </form>

                <span class="page-row__order"><?= $rowNumber ?></span>
            </div>
            <?php endforeach; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($pages === []): ?>
        <p class="empty">Du har ingen sider endnu.</p>
    <?php endif; ?>

    <p class="list-status" id="list-status" role="status" aria-live="polite"></p>

    <a class="create-link" href="create-page.php">opret side <span aria-hidden="true">+</span></a>
</main>

<script src="admin.js?v=3"></script>
</body>
</html>