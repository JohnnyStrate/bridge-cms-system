<?php
declare(strict_types=1);

/**
 * "Udgiv" — bygger websitet som statiske filer og lægger dem, om man vil,
 * op på webserveren via FTP.
 *
 * Tre handlinger, alle kun ved POST, så et genindlæst vindue ikke kører
 * dem igen:
 *
 *   build         Bygger export/ lokalt. Intet sendes nogen steder hen.
 *   publish       Bygger og uploader derefter det, der er ændret.
 *   publish_full  Bygger og uploader ALT forfra (ignorerer huskelisten).
 *
 * Både visning og kørsel ligger i samme fil, fordi resultatet skal vises
 * bagefter.
 *
 * "Sidst udgivet" sættes først, når handlingen er helt færdig. Ved en
 * upload betyder det: først når filerne er kommet over. Fejler FTP, står
 * siderne ikke som udgivet, selv om de blev bygget.
 */

require_once __DIR__ . '/../bootstrap.php';

$pdo            = Database::getConnection();
$pageRepository = new PageRepository($pdo);

$allPages  = $pageRepository->findAll();
$published = array_filter(
    $allPages,
    static fn (array $page): bool => $page['status'] === 'published'
);

$settings  = PublishSettings::load($pdo);
$canUpload = PublishSettings::isConfigured($settings);

$result = null;
$upload = null;
$error  = null;

/** "1 fil" / "3 filer" */
$count = static fn (int $n, string $one, string $many): string => $n . ' ' . ($n === 1 ? $one : $many);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'build');

    if (!in_array($action, ['build', 'publish', 'publish_full'], true)) {
        $action = 'build';
    }

    try {
        $exporter = new SiteExporter(
            $pageRepository,
            new BlockRepository($pdo),
            APP_ROOT . '/export',
            // Det aktive temas navbar og footer kommer med på hver side.
            new GlobalBlocks(new GlobalBlockRepository($pdo), ThemeRegistry::active($pdo)),
            GalleryMap::fromGalleries((new GalleryRepository($pdo))->all())
        );
        $result = $exporter->export();

        if ($action !== 'build') {
            if (!$canUpload) {
                throw new RuntimeException('Udfyld først oplysningerne under "Server (FTP)".');
            }

            $publisher = new FtpPublisher(
                $settings,
                new PublishFilesRepository($pdo),
                new SiteSettingsRepository($pdo)
            );
            $upload = $publisher->publish($result['dir'], $action === 'publish_full');
        }

        // Først nu — efter bygning og eventuel upload — noteres siderne
        // som udgivet.
        foreach ($result['page_ids'] as $pageId) {
            $pageRepository->markAsPublished($pageId);
        }

        // Listen genindlæses, så tidsstemplerne i tabellen er de nye.
        $allPages = $pageRepository->findAll();

    } catch (Throwable $e) {
        error_log('Udgivelse fejlede: ' . $e->getMessage());

        // Blev websitet bygget, men gik uploaden galt, så sig det: filerne
        // i export/ er i orden, og kun turen til serveren mangler.
        $error = ($result !== null
            ? 'Websitet blev bygget i export/, men uploaden til serveren fejlede. '
            : '') . $e->getMessage();
    }
}

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$noPages  = $published === [];
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udgiv</title>
    <link rel="stylesheet" href="../assets/css/fonts.css<?= PageRenderer::cacheBuster('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin">

<?php $activeMenu = 'export'; require __DIR__ . '/sidebar.php'; ?>

<main class="content">
    <h1 class="content__title">Udgiv website</h1>

    <?php if ($error !== null): ?>
        <p class="alert" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($upload !== null): ?>
        <div class="panel panel--success">
            <h2>Websitet er lagt op på serveren</h2>
            <p>
                <?= e($count($upload['uploaded'], 'fil', 'filer')) ?> uploadet,
                <?= e($count($upload['unchanged'], 'fil', 'filer')) ?> uændret<?php if ($upload['deleted'] > 0): ?>,
                <?= e($count($upload['deleted'], 'fil', 'filer')) ?> slettet fra serveren<?php endif; ?>
                (<?= e($upload['host']) ?>).
            </p>

            <?php if ($upload['warnings'] !== []): ?>
                <p><strong>Bemærkninger:</strong></p>
                <ul>
                    <?php foreach ($upload['warnings'] as $warning): ?>
                        <li><?= e($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <div class="panel panel--success">
            <h2>Websitet er bygget</h2>
            <p>
                <?= (int) $result['pages'] ?> side<?= $result['pages'] === 1 ? '' : 'r' ?>
                og <?= (int) $result['assets'] ?> fil<?= $result['assets'] === 1 ? '' : 'er' ?>
                blev skrevet til <code>export/</code>.
            </p>

            <?php if ($result['warnings'] !== []): ?>
                <p><strong>Bemærkninger:</strong></p>
                <ul>
                    <?php foreach ($result['warnings'] as $warning): ?>
                        <li><?= e($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p>
                <a class="btn btn--primary" href="<?= e($basePath) ?>/export/index.html"
                   target="_blank" rel="noopener">Åbn det færdige website</a>
            </p>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h2>Sådan virker det</h2>
        <p>
            Alle <strong>udgivne</strong> sider skrives som færdige HTML-filer i
            mappen <code>export/</code> sammen med den CSS og de billeder, de bruger.
            Kladder springes over.
        </p>
        <p>
            Den øverste udgivne side i <a href="index.php">Dine sider</a> bliver
            forsiden. Rækkefølgen ændrer du ved at trække i listen.
        </p>
        <p class="field__hint">
            Mappen tømmes hver gang, så filer fra en tidligere udgivelse
            ikke bliver hængende.
        </p>
    </div>

    <div class="panel">
        <h2>Webserver</h2>
        <?php if ($canUpload): ?>
            <p>
                Websitet lægges op på <code><?= e($settings['host']) ?></code>
                i mappen <code><?= e($settings['dir']) ?></code>
                (<?= $settings['secure'] ? 'krypteret med FTPS' : 'ukrypteret' ?>).
                <a href="publish-settings.php">Ret oplysningerne</a>
            </p>
            <p class="field__hint">
                Kun nye og ændrede filer uploades, så det går hurtigt efter første gang.
                Filer, der ikke er med i websitet mere, slettes på serveren — men kun dem,
                CMS'et selv har lagt derop. Alt andet i mappen bliver ikke rørt.
            </p>
        <?php else: ?>
            <p>
                Oplysningerne til jeres webhotel er ikke udfyldt endnu. Udfyld dem under
                <a href="publish-settings.php">Server (FTP)</a> for at kunne uploade herfra.
                Indtil da kan websitet bygges lokalt i <code>export/</code>.
            </p>
        <?php endif; ?>
    </div>

    <h2 class="section-heading">Sider der kommer med</h2>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Side</th>
                <th scope="col">Adresse</th>
                <th scope="col">Status</th>
                <th scope="col">Sidst udgivet</th>
            </tr>
        </thead>
        <tbody>
            <?php $isFirstPublished = true; ?>
            <?php foreach ($allPages as $page): ?>
                <?php
                    $isPublished = $page['status'] === 'published';
                    $isFront     = $isPublished && $isFirstPublished
                        && $page['parent_id'] === null;

                    if ($isFront) {
                        $isFirstPublished = false;
                    }
                ?>
                <tr class="<?= $isPublished ? '' : 'is-muted' ?>">
                    <td><?= e($page['title']) ?></td>
                    <td>
                        <code>
                            <?= $isFront ? '/' : '/' . e($page['slug']) . '/' ?>
                        </code>
                        <?= $isFront ? '<small>(forside)</small>' : '' ?>
                    </td>
                    <td>
                        <span class="badge badge--<?= e($page['status']) ?>">
                            <?= $isPublished ? 'udgivet' : 'kladde' ?>
                        </span>
                    </td>
                    <td>
                        <?= $page['last_published_at'] !== null
                            ? e(date('d/m/Y H:i', strtotime((string) $page['last_published_at'])))
                            : '<span class="is-muted">aldrig</span>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($noPages): ?>
        <p class="alert" role="alert">
            Ingen sider er udgivet endnu. Åbn en side i editoren og sæt status til
            "Udgivet", før du bygger websitet.
        </p>
    <?php endif; ?>

    <form method="post" class="actions">
        <?php if ($canUpload): ?>
            <button type="submit" name="action" value="publish" class="btn btn--primary"
                    <?= $noPages ? 'disabled' : '' ?>>
                Byg og upload til serveren
            </button>
        <?php endif; ?>

        <button type="submit" name="action" value="build"
                class="btn <?= $canUpload ? 'btn--ghost' : 'btn--primary' ?>"
                <?= $noPages ? 'disabled' : '' ?>>
            Byg website
        </button>
    </form>

    <?php if ($canUpload): ?>
        <form method="post" class="actions"
              onsubmit="return confirm('Upload ALLE filer igen? Det kan tage et par minutter, hvis der er mange billeder.');">
            <button type="submit" name="action" value="publish_full" class="btn btn--ghost"
                    <?= $noPages ? 'disabled' : '' ?>>
                Upload alt forfra
            </button>
            <span class="field__hint">
                Brug den, hvis noget er rettet eller slettet direkte på serveren, eller
                hvis websitet ser forkert ud derude.
            </span>
        </form>
    <?php endif; ?>
</main>

<script>
    // En upload kan tage lidt tid. Knapperne låses, når der trykkes, så
    // man ikke starter den samme kørsel to gange. Låsningen sker et øjeblik
    // senere, ellers ville den knap, der blev trykket på, ikke komme med
    // i formularen. Annulleres bekræftelsen, låses intet.
    document.querySelectorAll('form.actions').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented || !event.submitter) {
                return;
            }

            var pressed = event.submitter;

            setTimeout(function () {
                document.querySelectorAll('main button').forEach(function (button) {
                    button.disabled = true;
                });
                pressed.textContent = 'Arbejder …';
            }, 0);
        });
    });
</script>
</body>
</html>
