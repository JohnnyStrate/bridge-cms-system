<?php
/**
 * Template for Standard Bridge 2's navbar.
 *
 * @var array<int, array<string, mixed>> $links   label, href, active.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * Blokken selv er en "holder", der bliver stående i siden. Det er den
 * indre bjælke (.s2nav__bar), der bliver fast øverst, når man scroller —
 * så siden ikke hopper op, når bjælken forlader sin plads.
 */
$editing = $context->isInlineEditing();
?>
<nav class="block block--standard2-navbar"<?= eAttr(['style' => $cssVars]) ?> aria-label="Hovedmenu">
    <div class="s2nav__bar">
        <?php if ($links !== []): ?>
            <ul class="s2nav__links">
                <?php foreach ($links as $link): ?>
                    <li>
                        <a class="s2-underline<?= $link['active'] ? ' is-active' : '' ?>" href="<?= e($link['href']) ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>><?= e($link['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</nav>
<?php if (!$editing): ?>
<?php /*
    1. Bjælken toner ind oppefra, når siden åbner (ren CSS).
    2. Scroller man mere end bjælkens egen højde ned, bliver den fast
       øverst på skærmen og glider blødt ned. Tilbage i toppen lægger den
       sig på sin plads igen.
    Virker også i den eksporterede side. Uden script står navbaren bare,
    hvor den står.
*/ ?>
<script>
(function () {
    var nav = document.currentScript && document.currentScript.previousElementSibling;
    if (!nav) {
        return;
    }
    var bar = nav.querySelector('.s2nav__bar');
    var stuck = false;
    var ticking = false;

    function update() {
        ticking = false;
        // Holderen beholder bjælkens højde, så intet under den hopper.
        if (!stuck) {
            nav.style.minHeight = bar.offsetHeight + 'px';
        }
        var limit = nav.offsetTop + bar.offsetHeight * 1.5;
        var shouldStick = window.scrollY > limit;

        if (shouldStick !== stuck) {
            stuck = shouldStick;
            nav.classList.toggle('is-stuck', stuck);
        }
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(update);
        }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
}());
</script>
<?php endif; ?>
