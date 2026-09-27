<?php
/**
 * Template for tema 3's navbar.
 *
 * @var string                           $logo      Færdig, valideret URL.
 * @var string                           $logoAlt
 * @var string                           $homeHref  Første menupunkts adresse.
 * @var array<int, array<string, mixed>> $links     label, href, active.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * MOBILMENUEN VIRKER UDEN JAVASCRIPT — afkrydsningsfelt + :checked, så den
 * også virker i den eksporterede statiske side.
 */
$editing = $context->isInlineEditing();
?>
<nav class="block block--tema3-navbar"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <input class="t3nav__toggle" type="checkbox" id="t3nav-toggle">

    <div class="t3nav__bar">
        <?php /* Glansen, der fejer over bjælken, har sin egen boks, så
                 flappen under den aktive side ikke bliver klippet af. */ ?>
        <span class="t3nav__sheen" aria-hidden="true"></span>

        <label class="t3nav__burger" for="t3nav-toggle" aria-label="Vis eller skjul menuen">
            <span class="t3nav__burger-lines"><span></span><span></span><span></span></span>
            <span class="t3nav__burger-text">Menu</span>
        </label>

        <?php if ($links !== []): ?>
            <ul class="t3nav__links">
                <?php foreach ($links as $link): ?>
                    <li>
                        <a href="<?= e($link['href']) ?>"<?= $link['active'] ? ' class="is-active" aria-current="page"' : '' ?>><span class="t3nav__label"><?= e($link['label']) ?></span></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <a class="t3nav__logo" href="<?= e($homeHref) ?>">
        <img src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $editing ? ' title="Logoet skiftes under Indstillinger"' : '' ?>>
    </a>
</nav>
