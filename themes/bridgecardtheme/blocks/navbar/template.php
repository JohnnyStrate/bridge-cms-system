<?php
/**
 * Template for Bridge Card-navbaren.
 *
 * @var array<int, array<string, mixed>> $links  index, label, href, active
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * Mobilmenuen virker uden JavaScript (checkbox + :checked), så den også
 * virker i den eksporterede side.
 */
?>
<nav class="block block--bridgecardtheme-navbar"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <input class="bcnav__toggle" type="checkbox" id="bcnav-toggle">
    <label class="bcnav__burger" for="bcnav-toggle" aria-label="Vis eller skjul menuen">
        <span></span><span></span><span></span>
    </label>

    <?php if ($links !== []): ?>
        <ul class="bcnav__links">
            <?php foreach ($links as $link): ?>
                <li>
                    <a href="<?= e($link['href']) ?>"<?= !empty($link['active']) ? ' class="is-active" aria-current="page"' : '' ?><?= $context->inlineRow('links', (int) $link['index'], 'label', 'Menupunkt') ?>><?= e($link['label']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</nav>
