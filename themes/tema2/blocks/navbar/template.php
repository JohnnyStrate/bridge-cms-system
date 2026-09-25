<?php
/**
 * Template for tema 2's navbar.
 *
 * @var string                          $logo      Færdig, valideret URL.
 * @var string                          $logoAlt
 * @var string                          $homeHref  Første menupunkts adresse.
 * @var array<int, array<string, mixed>> $links    label, href, active.
 * @var string                          $cssVars
 * @var RenderContext                   $context
 *
 * MOBILMENUEN VIRKER UDEN JAVASCRIPT — afkrydsningsfelt + :checked, så den
 * også virker i den eksporterede statiske side.
 */
$editing = $context->isInlineEditing();
?>
<nav class="block block--tema2-navbar"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <input class="t2nav__toggle" type="checkbox" id="t2nav-toggle">

    <?php if ($logo !== '' || $editing): ?>
        <a class="t2nav__logo" href="<?= e($homeHref) ?>">
            <?php if ($logo !== ''): ?>
                <img src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $editing ? ' title="Logoet skiftes under Indstillinger"' : '' ?>>
            <?php else: ?>
                <span class="t2nav__logo-empty">Logo</span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <label class="t2nav__burger" for="t2nav-toggle" aria-label="Vis eller skjul menuen">
        <span></span><span></span><span></span>
    </label>

    <div class="t2nav__bar">
        <?php if ($links !== []): ?>
            <ul class="t2nav__links">
                <?php foreach ($links as $link): ?>
                    <li>
                        <a href="<?= e($link['href']) ?>"<?= $link['active'] ? ' class="is-active" aria-current="page"' : '' ?>><?= e($link['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</nav>
