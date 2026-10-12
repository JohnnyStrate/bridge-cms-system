<?php
declare(strict_types=1);

/**
 * "Server (FTP)" — oplysningerne til webhotellet.
 *
 * Her skriver man host, bruger, kodeord, mappe og om forbindelsen skal
 * være krypteret. "Gem" gemmer dem; "Test forbindelse" prøver dem af uden
 * at gemme noget, så man kan finde ud af, om de passer, før man udgiver.
 *
 * Både visning og kørsel ligger i samme fil, ligesom i export.php, fordi
 * resultatet af testen skal vises sammen med det, man skrev. Gem sender
 * videre (Post/Redirect/Get), så et genindlæst vindue ikke gemmer igen.
 *
 * KODEORDET sendes aldrig tilbage til formularen. Et tomt felt betyder
 * "behold det gemte". Se PublishSettings.
 */

require_once __DIR__ . '/../bootstrap.php';

$pdo = Database::getConnection();

// Sikrer, at databasen er migreret — ellers en tydelig besked.
ThemeRegistry::active($pdo);

$stored = PublishSettings::load($pdo);
$values = $stored;
$saved  = isset($_GET['gemt']);
$error  = null;
$tested = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');

    // Formularen vises igen med det, der blev skrevet — men aldrig med
    // kodeordet.
    $values = [
        'host'     => (string) ($_POST['host'] ?? ''),
        'port'     => (int) ($_POST['port'] ?? 21) ?: 21,
        'user'     => (string) ($_POST['user'] ?? ''),
        'password' => '',
        'dir'      => (string) ($_POST['dir'] ?? '/'),
        'secure'   => !empty($_POST['secure']),
    ];

    try {
        // Tomt kodeordsfelt → det gemte bruges.
        $clean = PublishSettings::clean($_POST, $stored['password']);

        if ($action === 'test') {
            $publisher = new FtpPublisher(
                $clean,
                new PublishFilesRepository($pdo),
                new SiteSettingsRepository($pdo)
            );
            $tested = $publisher->test();
        } else {
            PublishSettings::save($pdo, $clean);

            header('Location: publish-settings.php?gemt=1');
            exit;
        }
    } catch (PDOException $e) {
        // En databasefejl er også en RuntimeException, men dens tekst skal
        // ikke vises til brugeren. Derfor fanges den først.
        error_log('FTP-indstillinger: ' . $e->getMessage());
        $error = 'Indstillingerne kunne ikke gemmes. Prøv igen.';
    } catch (InvalidArgumentException | RuntimeException $e) {
        // Beskeder fra PublishSettings og FtpPublisher er skrevet til brugeren.
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log('FTP-indstillinger: ' . $e->getMessage());
        $error = 'Noget gik galt. Prøv igen.';
    }
}

$hasPassword = $stored['password'] !== '';
$requirement = FtpPublisher::requirementsError($values['secure']);
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server (FTP)</title>
    <link rel="stylesheet" href="../assets/css/fonts.css<?= PageRenderer::cacheBuster('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="admin.css<?= PageRenderer::cacheBuster('admin/admin.css') ?>">
</head>
<body class="admin">

<?php $activeMenu = 'server'; require __DIR__ . '/sidebar.php'; ?>

<main class="content">
    <h1 class="content__title">Server (FTP)</h1>

    <?php if ($saved): ?>
        <p class="alert alert--ok" role="status">Gemt. Prøv "Test forbindelse", eller gå til Udgiv.</p>
    <?php endif; ?>

    <?php if ($tested !== null): ?>
        <p class="alert alert--ok" role="status"><?= e($tested) ?></p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p class="alert" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($requirement !== null): ?>
        <p class="alert" role="alert"><?= e($requirement) ?></p>
    <?php endif; ?>

    <p class="intro">
        Her står oplysningerne til jeres webhotel. Dem får I af udbyderen, når I
        opretter en FTP-adgang. Under <a href="export.php">Udgiv</a> kan websitet
        derefter bygges og lægges op med ét klik.
    </p>

    <form method="post" class="create-form settings-form" autocomplete="off">

        <div class="settings-row">
            <div class="field">
                <label for="host">Serveradresse</label>
                <input type="text" id="host" name="host" maxlength="253"
                       placeholder="ftp.dinklub.dk"
                       value="<?= e($values['host']) ?>">
                <p class="field__hint">Kun navnet — uden <code>ftp://</code>.</p>
            </div>

            <div class="field">
                <label for="port">Port</label>
                <input type="text" id="port" name="port" maxlength="5" inputmode="numeric"
                       value="<?= e((string) $values['port']) ?>">
                <p class="field__hint">Næsten altid 21.</p>
            </div>
        </div>

        <div class="settings-row">
            <div class="field">
                <label for="user">Brugernavn</label>
                <input type="text" id="user" name="user" maxlength="100"
                       value="<?= e($values['user']) ?>">
            </div>

            <div class="field">
                <label for="password">Kodeord</label>
                <input type="password" id="password" name="password" maxlength="200"
                       autocomplete="new-password"
                       placeholder="<?= $hasPassword ? '•••••••• (gemt)' : '' ?>">
                <p class="field__hint">
                    <?php if ($hasPassword): ?>
                        Lad feltet stå tomt for at beholde det gemte kodeord.
                    <?php else: ?>
                        Gemmes i CMS'ets database på jeres egen computer — aldrig i git.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="field">
            <label for="dir">Mappe på serveren</label>
            <input type="text" id="dir" name="dir" maxlength="300"
                   placeholder="/public_html"
                   value="<?= e($values['dir']) ?>">
            <p class="field__hint">
                Hvor websitet skal ligge. Mappen skal findes i forvejen. Ofte hedder den
                <code>public_html</code>, <code>www</code> eller <code>htdocs</code>.
                Filer i mappen, som CMS'et ikke selv har lagt derop, bliver aldrig rørt.
            </p>
        </div>

        <div class="field">
            <label class="field__check">
                <input type="checkbox" name="secure" value="1" <?= $values['secure'] ? 'checked' : '' ?>>
                Krypteret forbindelse (FTPS)
            </label>
            <p class="field__hint">
                Anbefales. Uden den sendes kodeordet i klartekst over nettet. Slå den kun
                fra, hvis serveren ikke understøtter FTPS.
            </p>
        </div>

        <div class="actions">
            <button type="submit" name="action" value="save" class="btn btn--primary">Gem</button>
            <button type="submit" name="action" value="test" class="btn btn--ghost">Test forbindelse</button>
        </div>
    </form>
</main>

</body>
</html>
