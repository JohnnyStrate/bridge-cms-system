<?php
declare(strict_types=1);

/**
 * "Opret side" — brugeren vælger mellem en blank side og en skabelon.
 *
 * Filen viser kun formularen. Selve oprettelsen sker i store-page.php,
 * så et genindlæst vindue aldrig kan oprette den samme side to gange.
 */

require_once __DIR__ . '/../bootstrap.php';

$pdo       = Database::getConnection();
$templates = (new TemplateRepository($pdo))->findActive();

// Alle eksisterende sider kan vælges som forælder. En ny side har endnu
// ingen undersider, så der er intet at sortere fra.
$parentChoices = PageTree::choices((new PageRepository($pdo))->findAll());

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$error    = $_GET['fejl'] ?? null;
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opret side</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&display=swap" rel="stylesheet">
    <!--
        Tallet efter "?" tvinger browseren til at hente en frisk kopi af
        admin.css, når filen ændres. Uden det kan en browser blive ved
        med at vise en gemt (cachet) udgave, selvom filen på serveren er
        opdateret — det er dét, der gav den ustylede forhåndsvisning.
        Sæt tallet én op, hver gang admin.css ændres.
    -->
    <link rel="stylesheet" href="admin.css?v=3">
</head>
<body class="admin">

<nav class="sidebar">
    <p class="sidebar__brand">Adminpanel</p>
    <ul class="sidebar__nav">
        <li><a href="index.php">Dine sider</a></li>
        <li><a href="create-page.php" aria-current="page">Opret side</a></li>
        <li><a href="export.php">Udgiv</a></li>
        <li><a href="#">Galleri</a></li>
        <li><a href="#">Indstillinger</a></li>
    </ul>
</nav>

<main class="content">
    <h1 class="content__title">Opret side</h1>

    <?php if ($error !== null): ?>
        <p class="alert" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="store-page.php" class="create-form">

        <div class="field">
            <label for="title">Sidens titel</label>
            <input type="text" id="title" name="title" required maxlength="255"
                   placeholder="Fx Forside eller Kontakt">
            <p class="field__hint">
                Adressen dannes automatisk ud fra titlen og kan rettes senere.
            </p>
        </div>

        <div class="field">
            <label for="parent">Underside af</label>
            <select id="parent" name="parent_id">
                <option value="0">— ingen (ligger i roden) —</option>
                <?php foreach ($parentChoices as $choiceId => $choiceLabel): ?>
                    <option value="<?= (int) $choiceId ?>"><?= e($choiceLabel) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="field__hint">
                Undersider får deres egen mappe under forælderen, fx
                <code>om-os/bestyrelse/</code>.
            </p>
        </div>

        <h2 class="section-heading">Vælg udgangspunkt</h2>

        <!--
            Radioknapperne er skjult visuelt, men findes stadig i DOM'en,
            så tastatur og skærmlæser fungerer. Kortene er deres labels.
        -->
        <div class="choices">

            <label class="choice">
                <input type="radio" name="template_id" value="0" checked>
                <span class="choice__body">
                    <span class="choice__thumb choice__thumb--blank" aria-hidden="true">+</span>
                    <span class="choice__title">Blank side</span>
                    <span class="choice__text">
                        Start helt forfra. Du tilføjer selv sektionerne bagefter.
                    </span>
                </span>
            </label>

            <?php foreach ($templates as $template): ?>
                <?php
                    $thumbnail = (string) ($template['thumbnail'] ?? '');
                    $hasThumb  = $thumbnail !== ''
                        && is_file(APP_ROOT . '/' . ltrim($thumbnail, '/'));
                ?>
                <label class="choice">
                    <input type="radio" name="template_id"
                           value="<?= (int) $template['id'] ?>">
                    <span class="choice__body">
                        <?php if ($hasThumb): ?>
                            <img class="choice__thumb"
                                 src="<?= e($basePath . '/' . ltrim($thumbnail, '/')) ?>"
                                 alt="">
                        <?php else: ?>
                            <span class="choice__thumb choice__thumb--empty" aria-hidden="true">
                                Ingen forhåndsvisning
                            </span>
                        <?php endif; ?>

                        <span class="choice__title-row">
                            <span class="choice__title"><?= e($template['name']) ?></span>
                            <!--
                                Knappen er en <button> inde i <label>. Uden
                                preventDefault() i JavaScript ville browseren
                                sende klikket videre til radioknappen bagved,
                                fordi det er sådan et klik i en <label>
                                normalt opfører sig.
                            -->
                            <button type="button" class="choice__preview"
                                    data-preview-template="<?= (int) $template['id'] ?>"
                                    data-preview-name="<?= e($template['name']) ?>"
                                    aria-label="Forhåndsvis <?= e($template['name']) ?>"
                                    title="Forhåndsvis">
                                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </span>
                        <span class="choice__text">
                            <?= e((string) ($template['description'] ?? '')) ?>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <?php if ($templates === []): ?>
            <p class="field__hint">
                Der er ingen skabeloner endnu. Du kan stadig oprette en blank side.
            </p>
        <?php endif; ?>

        <div class="actions">
            <a class="btn btn--ghost" href="index.php">Annullér</a>
            <button type="submit" class="btn btn--primary">Opret side</button>
        </div>
    </form>
</main>

<!--
    Boksen med forhåndsvisningen. <dialog> giver dæmpet baggrund,
    fokusfælde og luk-med-Escape uden ekstra kode. Iframen får først en
    adresse, når brugeren klikker på et øje.
-->
<dialog class="preview-dialog" id="template-preview"
        aria-labelledby="template-preview-title">
    <header class="preview-dialog__header">
        <h2 class="preview-dialog__title" id="template-preview-title">Forhåndsvisning</h2>
        <button type="button" class="preview-dialog__close" data-preview-close
                aria-label="Luk forhåndsvisning">&times;</button>
    </header>
    <iframe class="preview-dialog__frame" title="Forhåndsvisning af skabelon"
            src="about:blank"></iframe>
</dialog>

<script src="create-page.js"></script>
</body>
</html>