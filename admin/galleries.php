<?php
declare(strict_types=1);

/**
 * Oversigt over gallerier: opret, redigér, slet.
 *
 * Oprettelse sker her og ikke i en egen fil, fordi den kun består af ét
 * felt. Efter en POST sendes brugeren videre med en redirect, så et
 * genindlæs ikke opretter galleriet igen.
 */

require_once __DIR__ . '/../bootstrap.php';

$galleries = new GalleryRepository(Database::getConnection());
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));

    if ($name === '') {
        $error = 'Galleriet skal have et navn.';
    } else {
        try {
            $id = $galleries->create(mb_substr($name, 0, 150));

            // Direkte videre til redigering — brugeren oprettede jo
            // galleriet for at lægge billeder i det.
            header('Location: gallery-edit.php?id=' . $id);
            exit;

        } catch (PDOException $e) {
            // 23000 = brud på den unikke nøgle på navnet.
            $error = $e->getCode() === '23000'
                ? 'Der findes allerede et galleri med det navn.'
                : 'Galleriet kunne ikke oprettes.';
        }
    }
}

$list = $galleries->all();
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallerier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin">

<nav class="sidebar">
    <p class="sidebar__brand">Adminpanel</p>
    <ul class="sidebar__nav">
        <li><a href="index.php">Dine sider</a></li>
        <li><a href="create-page.php">Opret side</a></li>
        <li><a href="export.php">Udgiv</a></li>
        <li><a href="galleries.php" aria-current="page">Galleri</a></li>
        <li><a href="#">Indstillinger</a></li>
    </ul>
</nav>

<main class="content">
    <h1 class="content__title">Gallerier</h1>

    <?php if ($error !== ''): ?>
        <p class="alert" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['fejl'])): ?>
        <p class="alert" role="alert"><?= e((string) $_GET['fejl']) ?></p>
    <?php endif; ?>

    <form class="gallery-new" method="post" action="galleries.php">
        <label for="new-name">Nyt galleri</label>
        <input type="text" id="new-name" name="name" maxlength="150"
               placeholder="Fx Billeder fra klubaften" required>
        <button type="submit" class="btn btn--primary">Opret</button>
    </form>

    <?php if ($list === []): ?>
        <p class="gallery-empty">Du har ikke oprettet nogen gallerier endnu.</p>
    <?php else: ?>
        <ul class="page-list">
            <?php foreach ($list as $gallery): ?>
                <?php $used = $galleries->usageCount((int) $gallery['id']); ?>
                <li class="page-row">
                    <span class="page-row__title"><?= e($gallery['name']) ?></span>

                    <span class="page-row__meta">
                        <?= count($gallery['images']) ?> billeder ·
                        <?= $used === 0
                            ? 'ikke i brug'
                            : 'brugt ' . $used . ' sted' . ($used === 1 ? '' : 'er') ?>
                    </span>

                    <a class="page-row__action" href="gallery-edit.php?id=<?= (int) $gallery['id'] ?>">
                        Redigér
                    </a>

                    <?php /*
                        Sletning er en POST, ikke et link. Et GET-link kan
                        følges af browserens forhåndshentning og ville kunne
                        slette galleriet, uden at nogen har klikket.
                    */ ?>
                    <form method="post" action="delete-gallery.php"
                          data-confirm="Slet galleriet &quot;<?= e($gallery['name']) ?>&quot;?<?= $used > 0 ? ' Det bruges ' . $used . ' sted(er) og forsvinder fra de sider.' : '' ?>"
                          onsubmit="return confirm(this.dataset.confirm);">
                        <input type="hidden" name="id" value="<?= (int) $gallery['id'] ?>">
                        <button type="submit" class="page-row__action page-row__action--danger">
                            Slet
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

</body>
</html>