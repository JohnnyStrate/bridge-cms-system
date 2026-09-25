<?php
/**
 * Adminpanelets menu i venstre side — ét sted for alle admin-sider.
 *
 * Brug:
 *     <?php $activeMenu = 'themes'; require __DIR__ . '/sidebar.php'; ?>
 *
 * $activeMenu er nøglen fra listen nedenfor. Et nyt menupunkt er én linje
 * her, og så står det på alle sider.
 *
 * @var string|null $activeMenu
 */

$menuItems = [
    'pages'    => ['index.php', 'Dine sider'],
    'create'   => ['create-page.php', 'Opret side'],
    'export'   => ['export.php', 'Udgiv'],
    'gallery'  => ['galleries.php', 'Galleri'],
    'themes'   => ['themes.php', 'Tema'],
    'settings' => ['settings.php', 'Indstillinger'],
];

$activeMenu = $activeMenu ?? '';
?>
<nav class="sidebar">
    <p class="sidebar__brand">Adminpanel</p>
    <ul class="sidebar__nav">
        <?php foreach ($menuItems as $key => [$href, $label]): ?>
            <li><a href="<?= e($href) ?>"<?= $key === $activeMenu ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>
