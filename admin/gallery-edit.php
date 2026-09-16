<?php
declare(strict_types=1);

/**
 * Redigering af ét galleri: navn og billeder.
 *
 * Billedrækkerne tegnes af den SAMME FieldRenderer som blokkenes felter,
 * ud fra GalleryRepository::imagesField(). Derfor virker rækker,
 * miniaturer og enkeltbillede-udskiftning her uden ny markup.
 *
 * Oven over rækkerne ligger en flerupload. Et galleri fyldes typisk med
 * tyve billeder ad gangen, og ét ad gangen ville gøre opgaven ubrugelig.
 * Den tilføjer én række pr. fil, når uploaden er færdig.
 */

require_once __DIR__ . '/../bootstrap.php';

$id        = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$galleries = new GalleryRepository(Database::getConnection());
$gallery   = $galleries->find($id);

if ($gallery === null) {
    http_response_code(404);
    exit('Galleriet blev ikke fundet.');
}

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$fields   = new FieldRenderer([], $basePath);
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigér: <?= e($gallery['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <?php /* Repeater- og billedfelterne bruger editorens styling. */ ?>
    <link rel="stylesheet" href="editor.css">
</head>
<body class="editor" data-base-path="<?= e($basePath) ?>">

<header class="ed-top">
    <a class="ed-back" href="galleries.php" aria-label="Tilbage til gallerier">&larr;</a>
    <h1 class="ed-title"><?= e($gallery['name']) ?></h1>
</header>

<section class="ed-settings" id="gallery-form" data-gallery-id="<?= (int) $gallery['id'] ?>">
    <p class="ed-field">
        <label for="gallery-name">Navn</label>
        <input type="text" id="gallery-name" data-gallery-field="name"
               value="<?= e($gallery['name']) ?>" maxlength="150">
    </p>
</section>

<main class="ed-canvas">
    <article class="ed-block">
        <span class="ed-block__label">Billeder</span>

        <div class="ed-panel">

            <?php /*
                Fil-inputtet ligger inde i etiketten, så et klik på hele
                feltet åbner filvælgeren uden JavaScript. Træk-og-slip
                håndteres af gallery.js.
            */ ?>
            <label class="gl-drop" id="gallery-drop">
                <input type="file" id="gallery-files" accept="image/*" multiple hidden>
                <span class="gl-drop__text">
                    Træk billeder herind — eller klik for at vælge flere
                </span>
                <span class="gl-drop__status" id="gallery-upload-status"></span>
            </label>

            <fieldset class="ed-group">
                <legend>Indhold</legend>
                <?= $fields->repeater('images', GalleryRepository::imagesField(), $gallery['images']) ?>
            </fieldset>
        </div>
    </article>
</main>

<footer class="ed-footer">
    <span class="ed-status" id="save-status" role="status" aria-live="polite"></span>
    <button type="button" class="btn btn--primary" id="save-btn" disabled>Gem</button>
</footer>

<script src="gallery.js"></script>
</body>
</html>