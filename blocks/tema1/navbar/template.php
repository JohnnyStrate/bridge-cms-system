<?php
/**
 * Template for tema 1's navbar.
 *
 * @var string                            $logo      Færdig, valideret URL.
 * @var string                            $logoAlt
 * @var string                            $brandText Vises kun uden logo.
 * @var array<int, array<string, string>> $links     Hver med label og href.
 * @var string                            $ctaLabel  Tom = ingen knap.
 * @var string                            $ctaHref
 * @var string                            $cssVars
 *
 * Adresserne er regnet ud i Tema1NavbarBlock::render(). Templaten kender
 * hverken sitets struktur eller sin egen placering i den.
 *
 * MOBILMENUEN VIRKER UDEN JAVASCRIPT.
 * Afkrydsningsfeltet er skjult, og etiketten (burgeren) slår det til og fra.
 * CSS'en reagerer på :checked. Derfor virker menuen også i den eksporterede
 * statiske side, hvor der ikke kører noget JavaScript.
 */
?>
<nav class="block block--tema1-navbar"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <input class="t1nav__toggle" type="checkbox" id="t1nav-toggle">

    <div class="t1nav__inner">

        <a class="t1nav__brand" href="<?= e($links[0]['href'] ?? '#') ?>">
            <?php if ($logo !== ''): ?>
                <img class="t1nav__logo" src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>">
            <?php elseif ($brandText !== ''): ?>
                <span class="t1nav__mark" aria-hidden="true"><?= e(mb_substr($brandText, 0, 1)) ?></span>
                <span class="t1nav__name"><?= e($brandText) ?></span>
            <?php endif; ?>
        </a>

        <?php /* Burgeren er etiketten til afkrydsningsfeltet ovenfor. */ ?>
        <label class="t1nav__burger" for="t1nav-toggle" aria-label="Vis eller skjul menuen">
            <span></span><span></span><span></span>
        </label>

        <div class="t1nav__panel">
            <?php if ($links !== []): ?>
                <ul class="t1nav__links">
                    <?php foreach ($links as $index => $link): ?>
                        <?php /* --i styrer, hvornår punktet toner ind. */ ?>
                        <li style="--i:<?= (int) $index ?>">
                            <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($ctaLabel !== ''): ?>
                <a class="t1nav__cta" href="<?= e($ctaHref) ?>"><?= e($ctaLabel) ?></a>
            <?php endif; ?>
        </div>
    </div>
</nav>
