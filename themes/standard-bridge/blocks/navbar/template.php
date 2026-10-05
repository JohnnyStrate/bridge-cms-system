<?php
/**
 * Template for standardbridge-navbar.
 *
 * @var array<int, array<string, mixed>> $links    index, label, href, active
 * @var string                           $logo     Færdig URL eller ''.
 * @var string                           $logoAlt
 * @var string                           $logoUrl  Tom = intet link.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * Mobilmenuen virker uden JavaScript: en skjult afkrydsningsboks åbner og
 * lukker den (se block.css). Så virker den også på den udgivne side.
 */
$editing = $context->isInlineEditing();
?>
<nav class="block sbnav<?= $editing ? '' : ' sbnav--animate' ?>"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <input class="sbnav__toggle" type="checkbox" id="sbnav-toggle">
    <label class="sbnav__burger" for="sbnav-toggle" aria-label="Vis eller skjul menuen">
        <span></span><span></span><span></span>
    </label>

    <?php if ($links !== []): ?>
        <ul class="sbnav__links">
            <?php foreach ($links as $n => $link): ?>
                <li style="--i:<?= (int) $n ?>">
                    <a href="<?= e($link['href']) ?>"<?= $link['active'] ? ' class="is-active" aria-current="page"' : '' ?><?= $context->inlineRow('links', (int) $link['index'], 'label', 'Menupunkt') ?>><?= e($link['label']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($logo !== '' || $editing): ?>
        <?php if ($logoUrl !== ''): ?>
            <a class="sbnav__logo" href="<?= e($logoUrl) ?>">
        <?php else: ?>
            <span class="sbnav__logo">
        <?php endif; ?>
            <img src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $logo === '' ? ' hidden' : '' ?><?= $context->inlineImage('logo') ?>>
        <?= $logoUrl !== '' ? '</a>' : '</span>' ?>
    <?php endif; ?>
</nav>
