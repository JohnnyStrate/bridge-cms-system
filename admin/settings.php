<?php
declare(strict_types=1);

/**
 * "Indstillinger" — klubbens faste oplysninger.
 *
 * Navn, logo, adresse, telefon og e-mail skrives her ét sted. Alle temaers
 * navbar og footer henter dem herfra (SiteInfo), så de følger med, når man
 * skifter tema.
 *
 * Filen viser kun formularen. Selve gemningen sker i save-settings.php.
 */

require_once __DIR__ . '/../bootstrap.php';

// Sikrer, at databasen er migreret — ellers en tydelig besked.
ThemeRegistry::active(Database::getConnection());

$info  = SiteInfo::all();
$saved = isset($_GET['gemt']);
$error = $_GET['fejl'] ?? null;

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indstillinger</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css<?= PageRenderer::cacheBuster('admin/admin.css') ?>">
</head>
<body class="admin">

<?php $activeMenu = 'settings'; require __DIR__ . '/sidebar.php'; ?>

<main class="content">
    <h1 class="content__title">Indstillinger</h1>

    <?php if ($saved): ?>
        <p class="alert alert--ok" role="status">Gemt. Navbar og footer er opdateret i alle temaer.</p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p class="alert" role="alert"><?= e((string) $error) ?></p>
    <?php endif; ?>

    <p class="intro">
        Klubbens oplysninger skrives her ét sted. De vises i navbar og footer
        i alle temaer og følger med, når du skifter tema.
    </p>

    <form method="post" action="save-settings.php" enctype="multipart/form-data" class="create-form settings-form">

        <div class="field">
            <label for="club_name">Klubbens navn</label>
            <input type="text" id="club_name" name="club_name" maxlength="120"
                   value="<?= e($info['club_name']) ?>">
            <p class="field__hint">
                Bruges i footerens bundlinje: skriv <code>{klub}</code> dér, så
                indsættes navnet automatisk.
            </p>
        </div>

        <div class="field">
            <span class="field__label">Logo</span>
            <div class="settings-logo">
                <?php if ($info['logo'] !== ''): ?>
                    <img class="settings-logo__img"
                         src="<?= e($basePath . '/' . ltrim($info['logo'], '/')) ?>" alt="Nuværende logo">
                <?php else: ?>
                    <span class="settings-logo__img settings-logo__img--empty">Intet logo</span>
                <?php endif; ?>

                <div>
                    <label class="btn btn--ghost settings-logo__pick" for="logo">Vælg nyt logo …</label>
                    <input class="settings-logo__file" type="file" id="logo" name="logo"
                           accept="image/png,image/jpeg,image/webp,image/gif">
                    <?php if ($info['logo'] !== ''): ?>
                        <label class="settings-logo__remove">
                            <input type="checkbox" name="remove_logo" value="1"> Fjern logoet
                        </label>
                    <?php endif; ?>
                    <p class="field__hint">
                        PNG, JPG, WebP eller GIF. Uden logo viser temaerne deres
                        egen pladsholder eller klubbens navn.
                    </p>
                </div>
            </div>
        </div>

        <div class="field">
            <label for="address">Adresse</label>
            <textarea id="address" name="address" rows="3" maxlength="300"><?= e($info['address']) ?></textarea>
            <p class="field__hint">Linjeskift bliver til linjeskift i footeren.</p>
        </div>

        <div class="settings-row">
            <div class="field">
                <label for="phone">Telefon</label>
                <input type="text" id="phone" name="phone" maxlength="40"
                       value="<?= e($info['phone']) ?>">
            </div>

            <div class="field">
                <label for="email">E-mail</label>
                <input type="text" id="email" name="email" maxlength="254" inputmode="email"
                       value="<?= e($info['email']) ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn--primary">Gem</button>
        </div>
    </form>
</main>

<script>
    // Viser det valgte filnavn på knappen, så man kan se, at noget er valgt.
    document.getElementById('logo').addEventListener('change', function () {
        var name = this.files && this.files[0] ? this.files[0].name : '';
        document.querySelector('.settings-logo__pick').textContent = name || 'Vælg nyt logo …';
    });
</script>
</body>
</html>
