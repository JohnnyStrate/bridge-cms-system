<?php
declare(strict_types=1);

/**
 * Editoren.
 *
 * Sidens tilstand holdes i browseren. Serveren leverer udgangspunktet og
 * de formularfelter, hver bloktype har brug for; JavaScript holder styr
 * på hvad der er ændret og sender det hele samlet, når brugeren gemmer.
 *
 * Formularfelterne bygges her i PHP ud fra blokkenes skemaer — ikke i
 * JavaScript. Skemaet er sandheden om, hvilke felter der findes, og den
 * viden skal kun ligge ét sted.
 *
 * GLOBALE BLOKKE
 * Navbaren ligger i laerredet som alle andre blokke, så editoren stadig
 * ligner den færdige side. Den er bare markeret med data-global-slot og
 * gemmes i global_blocks på det aktive tema — altså på alle sider på én gang.
 *
 * TEMA
 * "+"-menuen viser kun det aktive temas blokke og de fælles. Blokke fra et
 * andet tema, der allerede ligger på siden, vises og kan redigeres som før.
 */

require_once __DIR__ . '/../bootstrap.php';

$pageId = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT) ?: 0;

$pdo             = Database::getConnection();
$pageRepository  = new PageRepository($pdo);
$blockRepository = new BlockRepository($pdo);
$theme           = ThemeRegistry::active($pdo);
$themeClass      = ThemeRegistry::get($theme);
$globalBlocks    = new GlobalBlocks(new GlobalBlockRepository($pdo), $theme);

$page = $pageRepository->find($pageId);

if ($page === null) {
    http_response_code(404);
    exit('Siden blev ikke fundet.');
}

$blocks   = $blockRepository->findByPage($pageId);
$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

$allPages = $pageRepository->findAll();

// Sitets struktur skal med, for at menupunkter kan slå deres målside op.
$siteMap = SiteMap::fromPages($allPages);

// En side må ikke kunne vælge sig selv eller en af sine egne undersider
// som forælder — det ville gøre forældrekæden cyklisk. De sorteres fra
// her, så valget slet ikke kan træffes, frem for kun at blive afvist
// bagefter af PageSaver.
$parentChoices = PageTree::choices(
    $allPages,
    array_merge([$pageId], $pageRepository->descendantIds($pageId))
);
// Feltrendereren kender listen over sider, så et side-felt kan tegnes
// som en dropdown frem for et tekstfelt, man kan stave forkert i.
// Gallerierne slås op ét sted og sendes både til rendering (så blokken
// kan tegne billederne) og til feltrendereren (så dropdownen har dem).
$galleryMap = GalleryMap::fromGalleries((new GalleryRepository($pdo))->all());

$context = RenderContext::editor($basePath, $siteMap, $galleryMap)
    ->withInlineEditing()
    ->withCurrentPage($pageId);
$fields = new FieldRenderer($siteMap->choices(), $basePath, $galleryMap->choices());

// "+"-menuens grupper: det aktive temas blokke og de fælles, uden de
// globale — dem tilføjer man med knapperne øverst i menuen.
$menuGroups = [];

foreach (BlockRegistry::grouped($theme) as $groupName => $groupBlocks) {
    $choices = array_filter(
        $groupBlocks,
        static fn (string $type): bool => !GlobalBlocks::isManaged($type),
        ARRAY_FILTER_USE_KEY
    );

    if ($choices !== []) {
        $menuGroups[$groupName] = $choices;
    }
}

// Det aktive temas synlige navbar og footer. Mangler en slot her, har
// brugeren fjernet den, og "+"-menuen tilbyder at tilføje den igen.
$savedGlobals = $globalBlocks->visible();

/**
 * Tegner én global blok som editor-element.
 *
 * Samme markup bruges til den gemte blok og til <template>-skabelonen,
 * så de to aldrig kan skride fra hinanden. $row === null betyder "ny
 * blok" og giver blokkens standardværdier.
 *
 * Bloktypen bestemmes af det aktive tema, ikke af rækken.
 */
$globalArticle = static function (string $slot, array $def, ?array $row)
    use ($fields, $context, $globalBlocks): string {

    $type  = $globalBlocks->type($slot);
    $class = $type === null ? null : BlockRegistry::get($type);

    if ($class === null) {
        return '';
    }

    // Rækker fra GlobalBlocks er allerede valideret mod skemaet.
    $settings = $row === null ? $class::defaultSettings() : $row['settings'];
    $styles   = $row === null ? $class::defaultStyles() : $row['styles'];

    // Ingen op/ned-knapper: en global bloks plads bestemmes af dens slot,
    // ikke af rækkefølgen på den enkelte side.
    return '<article class="ed-block ed-block--global"'
        . ' data-global-slot="' . e($slot) . '"'
        . ' data-global-position="' . e($def['position']) . '"'
        . ' data-block-type="' . e($type) . '">'
        . '<span class="ed-block__label">' . e($class::label())
        . ' <span class="ed-block__badge">' . e($def['hint']) . '</span></span>'
        . '<div class="ed-block__actions">'
        . '<button type="button" class="ed-btn ed-btn--styles" data-action="styles"'
        . ' aria-expanded="false" title="Udseende">&#127912;</button>'
        . '<button type="button" class="ed-btn ed-btn--edit" data-action="edit"'
        . ' aria-expanded="false" title="Alle felter">&#9998;</button>'
        . '<button type="button" class="ed-btn ed-btn--delete" data-action="delete">&times;</button>'
        . '</div>'
        . '<div class="ed-block__preview">'
        . $class::render($settings, $styles, $context)
        . '</div>'
        . $fields->panel($class, $settings, $styles)
        . '</article>';
};

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rediger: <?= e($page['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
        <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="admin.css<?= PageRenderer::cacheBuster('admin/admin.css') ?>">
    <link rel="stylesheet" href="editor.css<?= PageRenderer::cacheBuster('admin/editor.css') ?>">

    <?php /*
        CSS for ALLE bloktyper — ikke kun dem, der ligger på siden nu.
        Brugeren kan tilføje en hvilken som helst blok uden at genindlæse,
        og dens styling skal være på plads i det øjeblik den dukker op.
    */ ?>
    <?php foreach (PageRenderer::allStylesheets() as $sheet): ?>
        <link rel="stylesheet" href="<?= e($basePath . '/' . $sheet . PageRenderer::cacheBuster($sheet)) ?>">
    <?php endforeach; ?>
</head>
<body class="editor" data-page-id="<?= (int) $page['id'] ?>"
      data-base-path="<?= e($basePath) ?>">

<header class="ed-top">
    <a class="ed-back" href="index.php" aria-label="Tilbage til dine sider">&larr;</a>
    <h1 class="ed-title"><?= e($page['title']) ?></h1>
    <?php if ($themeClass !== null): ?>
        <a class="ed-theme" href="themes.php" title="Skift tema">Tema: <?= e($themeClass::name()) ?></a>
    <?php endif; ?>
</header>

<section class="ed-settings">
    <p class="ed-field">
        <label for="page-title">Titel</label>
        <input type="text" id="page-title" data-page-field="title"
               value="<?= e($page['title']) ?>" maxlength="255">
    </p>
    <p class="ed-field">
        <label for="page-slug">Webadresse</label>
        <input type="text" id="page-slug" data-page-field="slug"
               value="<?= e($page['slug']) ?>" maxlength="255">
    </p>
    <p class="ed-field">
        <label for="page-parent">Underside af</label>
        <select id="page-parent" data-page-field="parent_id">
            <option value="0">— ingen (ligger i roden) —</option>
            <?php foreach ($parentChoices as $choiceId => $choiceLabel): ?>
                <option value="<?= (int) $choiceId ?>"
                    <?= (int) ($page['parent_id'] ?? 0) === (int) $choiceId ? 'selected' : '' ?>>
                    <?= e($choiceLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="ed-field">
        <label for="page-status">Status</label>
        <select id="page-status" data-page-field="status">
            <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Kladde</option>
            <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Udgivet</option>
        </select>
    </p>
</section>

<main class="ed-canvas" id="canvas">

    <?php /* Globale blokke der ligger FØR sidens eget indhold. */ ?>
    <?php foreach (GlobalBlocks::SLOTS as $slot => $def): ?>
        <?php if ($def['position'] !== 'before' || !isset($savedGlobals[$slot])) {
            continue;
        } ?>
        <?= $globalArticle($slot, $def, $savedGlobals[$slot]) ?>
    <?php endforeach; ?>

    <?php foreach ($blocks as $block): ?>
        <?php
            $class = BlockRegistry::get((string) $block['block_type']);
            if ($class === null) {
                continue;
            }
            $settings = FieldValidator::validateAll($class::getSchema(), $block['settings']);
            $styles   = FieldValidator::validateAll($class::getStyleSchema(), $block['styles']);
        ?>
        <article class="ed-block"
                 data-block-id="<?= (int) $block['id'] ?>"
                 data-block-type="<?= e($block['block_type']) ?>">

            <span class="ed-block__label"><?= e($class::label()) ?></span>

            <div class="ed-block__actions">
                <button type="button" class="ed-btn ed-btn--styles" data-action="styles"
                        aria-expanded="false" title="Udseende">&#127912;</button>
                <button type="button" class="ed-btn ed-btn--edit" data-action="edit"
                        aria-expanded="false" title="Alle felter">&#9998;</button>
                <button type="button" class="ed-btn ed-btn--move" data-action="up">&and;</button>
                <button type="button" class="ed-btn ed-btn--move" data-action="down">&or;</button>
                <button type="button" class="ed-btn ed-btn--delete" data-action="delete">&times;</button>
            </div>

            <div class="ed-block__preview">
                <?= $class::render($settings, $styles, $context) ?>
            </div>

            <?= $fields->panel($class, $settings, $styles) ?>
        </article>
    <?php endforeach; ?>

    <?php /* Globale blokke der ligger EFTER sidens eget indhold, fx en footer. */ ?>
    <?php foreach (GlobalBlocks::SLOTS as $slot => $def): ?>
        <?php if ($def['position'] !== 'after' || !isset($savedGlobals[$slot])) {
            continue;
        } ?>
        <?= $globalArticle($slot, $def, $savedGlobals[$slot]) ?>
    <?php endforeach; ?>

</main>

<section class="ed-add">
    <button type="button" class="ed-add__toggle" id="add-toggle" aria-expanded="false">+</button>

    <div class="ed-add__menu" id="add-menu" hidden>

        <?php /*
            Globale blokke står først og er markeret: én knap pr. slot, med
            det aktive temas navbar og footer. Er blokken allerede på siden,
            er knappen slået fra — så brugeren kan se, at den findes, frem
            for at tro at den mangler.
        */ ?>
        <?php foreach (GlobalBlocks::SLOTS as $slot => $def): ?>
            <?php
                $globalType = $globalBlocks->type($slot);
                $class      = $globalType === null ? null : BlockRegistry::get($globalType);
            ?>
            <?php if ($class === null) {
                continue;
            } ?>
            <button type="button" class="ed-add__choice ed-add__choice--global"
                    data-add-global="<?= e($slot) ?>"
                    data-global-type="<?= e($globalType) ?>"
                    data-global-position="<?= e($def['position']) ?>"
                    <?= isset($savedGlobals[$slot]) ? 'disabled' : '' ?>>
                <?= e($class::label()) ?>
                <span class="ed-add__note"><?= e($def['hint']) ?></span>
            </button>
        <?php endforeach; ?>

        <?php /*
            Resten: det aktive temas blokke og de fælles.

            Har en gruppe kun globale blokke tilbage efter frasorteringen,
            vises overskriften ikke.
        */ ?>
        <?php foreach ($menuGroups as $groupName => $choices): ?>
            <p class="ed-add__group"><?= e($groupName) ?></p>
            <?php foreach ($choices as $type => $label): ?>
                <button type="button" class="ed-add__choice" data-add-type="<?= e($type) ?>">
                    <?= e($label) ?>
                </button>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</section>

<footer class="ed-footer">
    <span class="ed-status" id="save-status" role="status" aria-live="polite"></span>
    <div>
                <a href="index.php"><button type="button" class="btn btn--ghost" id="dinesider-btn"> <i class="fa-solid fa-arrow-left"></i>
                Tilbage til dine sider </button></a> 

    </div>   
     <div>
         <button type="button" class="btn btn--ghost" id="preview-btn">Forhåndsvis <i class="fa-solid fa-eye"></i> </button>
    <button type="button" class="btn btn--primary" id="save-btn" disabled>Gem</button>
     </div>

   
</footer>
<!-- <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js"></script> -->


<?php
/*
 * Skabeloner til nye blokke.
 *
 * Hver bloktype ligger klar som en <template> med sit forhåndsvisning og
 * sine felter, udfyldt med standardværdier. Når brugeren tilføjer en blok,
 * kloner JavaScript den tilsvarende skabelon.
 *
 * Alternativet — at hente markup fra serveren ved hvert klik — ville koste
 * et netværkskald og en ekstra fil for præcis samme resultat.
 */
?>
<?php foreach (array_merge(...array_values($menuGroups)) as $type => $label): ?>
    <?php
        $class    = BlockRegistry::get($type);
        $defaults = $class::defaultSettings();
        $dStyles  = $class::defaultStyles();
    ?>
    <template data-template-for="<?= e($type) ?>">
        <article class="ed-block" data-block-id="" data-block-type="<?= e($type) ?>">
            <span class="ed-block__label"><?= e($label) ?></span>
            <div class="ed-block__actions">
                <button type="button" class="ed-btn ed-btn--styles" data-action="styles"
                        aria-expanded="false" title="Udseende">&#127912;</button>
                <button type="button" class="ed-btn ed-btn--edit" data-action="edit"
                        aria-expanded="false" title="Alle felter">&#9998;</button>
                <button type="button" class="ed-btn ed-btn--move" data-action="up">&and;</button>
                <button type="button" class="ed-btn ed-btn--move" data-action="down">&or;</button>
                <button type="button" class="ed-btn ed-btn--delete" data-action="delete">&times;</button>
            </div>
            <div class="ed-block__preview">
                <?= $class::render($defaults, $dStyles, $context) ?>
            </div>
            <?= $fields->panel($class, $defaults, $dStyles) ?>
        </article>
    </template>
<?php endforeach; ?>

<?php /*
    Skabeloner til de globale blokke — én pr. slot, med det aktive temas
    bloktype. Nøglen er "slot:type", og editor.js slår op med samme nøgle.
*/ ?>
<?php foreach (GlobalBlocks::SLOTS as $slot => $def): ?>
    <?php $globalType = $globalBlocks->type($slot); ?>
    <?php if ($globalType === null) {
        continue;
    } ?>
    <template data-global-template-for="<?= e($slot . ':' . $globalType) ?>"><?= $globalArticle($slot, $def, null) ?></template>
<?php endforeach; ?>

<script src="editor.js<?= PageRenderer::cacheBuster('admin/editor.js') ?>"></script>
</body>
</html>