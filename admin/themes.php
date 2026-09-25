<?php
declare(strict_types=1);

/**
 * "Tema" — vælg sitets tema.
 *
 * Sitet har ét aktivt tema ad gangen. Temaet bestemmer navbar og footer på
 * alle sider, og hvilke blokke og skabeloner editoren tilbyder.
 *
 * Filen viser kun valget. Selve skiftet sker i activate-theme.php.
 */

require_once __DIR__ . '/../bootstrap.php';

$pdo    = Database::getConnection();
$active = ThemeRegistry::active($pdo);
// Ufærdige temaer (isReady() = false) vises ikke — medmindre de er aktive.
$themes = ThemeRegistry::selectable($active);

$switched = isset($_GET['skiftet']);
$error    = $_GET['fejl'] ?? null;

/**
 * Farverne til temaets lille miniature, læst fra navbarens og footerens
 * egne standardværdier. Så passer miniaturen altid til blokkene.
 *
 * @param class-string<ThemeInterface> $theme
 * @return array{top: string, bottom: string, accent: string}
 */
$swatch = static function (string $theme): array {
    $colorOf = static function (?string $type, string $field, string $fallback): string {
        $class = $type === null ? null : BlockRegistry::get($type);
        $value = $class === null ? '' : (string) ($class::defaultStyles()[$field] ?? '');

        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) === 1 ? $value : $fallback;
    };

    $slug   = $theme::slug();
    $header = GlobalBlocks::typeFor('header', $slug);
    $footer = GlobalBlocks::typeFor('footer', $slug);

    return [
        // Temaer med en gradient-navbar har ingen background_color, så
        // gradientens første farve bruges i stedet.
        'top'    => $colorOf($header, 'background_color', $colorOf($header, 'gradient_start', '#d5dae0')),
        'bottom' => $colorOf($footer, 'background_color', $colorOf($footer, 'gradient_start', '#d5dae0')),
        'accent' => $colorOf($header, 'accent_color', $colorOf($header, 'gradient_end', $colorOf($header, 'text_color', '#8a94a0'))),
    ];
};
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css<?= PageRenderer::cacheBuster('admin/admin.css') ?>">
</head>
<body class="admin">

<?php $activeMenu = 'themes'; require __DIR__ . '/sidebar.php'; ?>

<main class="content">
    <h1 class="content__title">Tema</h1>

    <?php if ($switched && isset($themes[$active])): ?>
        <p class="alert alert--ok" role="status">
            Sitet bruger nu <strong><?= e($themes[$active]::name()) ?></strong>.
        </p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p class="alert" role="alert"><?= e((string) $error) ?></p>
    <?php endif; ?>

    <p class="intro">
        Sitet bruger ét tema ad gangen. Temaet bestemmer navbar og footer på
        alle sider og hvilke sektioner og skabeloner, du kan vælge i editoren.
    </p>

    <form method="post" action="activate-theme.php" class="create-form" id="theme-form"
          data-active-theme="<?= e($active) ?>">

        <div class="choices">
            <?php foreach ($themes as $slug => $theme): ?>
                <?php
                    $colors    = $swatch($theme);
                    $thumbnail = $theme::thumbnail();
                    $hasThumb  = $thumbnail !== ''
                        && is_file(APP_ROOT . '/' . ltrim($thumbnail, '/'));

                    // Øjet viser temaets første skabelon med temaets egen
                    // navbar og footer.
                    $previewSlug = (string) (array_key_first(TemplateRegistry::forTheme($slug)) ?? '');
                ?>
                <label class="choice">
                    <input type="radio" name="theme" value="<?= e($slug) ?>"
                           data-theme-name="<?= e($theme::name()) ?>"
                           <?= $slug === $active ? 'checked' : '' ?>>
                    <span class="choice__body">
                        <?php if ($hasThumb): ?>
                            <img class="choice__thumb" src="<?= e('../' . ltrim($thumbnail, '/')) ?>" alt="">
                        <?php else: ?>
                            <span class="choice__thumb theme-swatch" aria-hidden="true"
                                  style="--top: <?= e($colors['top']) ?>; --bottom: <?= e($colors['bottom']) ?>; --accent: <?= e($colors['accent']) ?>">
                                <span class="theme-swatch__bar"><i></i><b></b><b></b><b></b></span>
                                <span class="theme-swatch__body"><b></b><b></b><b></b></span>
                                <span class="theme-swatch__foot"></span>
                            </span>
                        <?php endif; ?>

                        <span class="choice__title-row">
                            <span class="choice__title">
                                <?= e($theme::name()) ?>
                                <?php if ($slug === $active): ?>
                                    <span class="badge">Aktivt</span>
                                <?php endif; ?>
                            </span>
                            <?php if ($previewSlug !== ''): ?>
                                <button type="button" class="choice__preview"
                                        data-preview-template="<?= e($previewSlug) ?>"
                                        data-preview-name="<?= e($theme::name()) ?>"
                                        aria-label="Forhåndsvis <?= e($theme::name()) ?>"
                                        title="Forhåndsvis">
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"
                                         fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </span>
                        <span class="choice__text"><?= e($theme::description()) ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="notice">
            <p class="notice__title">Sådan virker et skift</p>
            <ul>
                <li>Navbar og footer skifter til det nye temas på alle sider.
                    Første gang får de temaets eksempelindhold.</li>
                <li>Det gamle temas navbar og footer bliver gemt, så du kan
                    skifte tilbage uden at miste noget.</li>
                <li>Dine sider beholder deres sektioner. Nye sider laves med
                    det nye temas skabeloner.</li>
            </ul>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn--primary">Brug valgt tema</button>
        </div>
    </form>
</main>

<dialog class="preview-dialog" id="template-preview"
        aria-labelledby="template-preview-title">
    <header class="preview-dialog__header">
        <h2 class="preview-dialog__title" id="template-preview-title">Forhåndsvisning</h2>
        <button type="button" class="preview-dialog__close" data-preview-close
                aria-label="Luk forhåndsvisning">&times;</button>
    </header>
    <iframe class="preview-dialog__frame" title="Forhåndsvisning af tema"
            src="about:blank"></iframe>
</dialog>

<script>
    // Et skift rører navbar og footer på hele sitet. Det skal brugeren
    // bekræfte — men kun når der faktisk vælges et andet tema.
    document.getElementById('theme-form').addEventListener('submit', function (event) {
        const chosen = this.querySelector('input[name="theme"]:checked');

        if (!chosen || chosen.value === this.dataset.activeTheme) {
            event.preventDefault();
            return;
        }

        const question = 'Skift sitet til "' + chosen.dataset.themeName + '"?\n\n'
            + 'Navbar og footer skifter på alle sider. Det nuværende temas '
            + 'navbar og footer bliver gemt, så du kan skifte tilbage.';

        if (!confirm(question)) {
            event.preventDefault();
        }
    });
</script>
<script src="create-page.js"></script>
</body>
</html>
