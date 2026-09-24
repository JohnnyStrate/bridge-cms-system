<?php
/**
 * Template for tema 2's kort med ikoner.
 *
 * @var string                           $eyebrow
 * @var string                           $title
 * @var array<int, array<string, mixed>> $cards  index, icon (SVG fra ICONS), title, text, buttonLabel, href
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--tema2-cards"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t2c__inner">

        <header class="t2c__head">
            <?php if ($eyebrow !== '' || $editing): ?>
                <p class="t2c__eyebrow"<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></p>
            <?php endif; ?>
            <h2 class="t2c__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
        </header>

        <?php if ($cards !== []): ?>
            <div class="t2c__grid">
                <?php foreach ($cards as $card): ?>
                    <article class="t2c__card" style="--i:<?= (int) $card['index'] ?>">
                        <?php if ($card['icon'] !== ''): ?>
                            <svg class="t2c__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= $card['icon'] ?></svg>
                        <?php endif; ?>

                        <h3 class="t2c__card-title"<?= $context->inlineRow('cards', (int) $card['index'], 'title', 'Titel') ?>><?= e($card['title']) ?></h3>
                        <p class="t2c__card-text"<?= $context->inlineRow('cards', (int) $card['index'], 'text', 'Kort tekst') ?>><?= e($card['text']) ?></p>

                        <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                            <a class="t2c__button" href="<?= e($card['href']) ?>">
                                <span class="t2c__button-label"<?= $context->inlineRow('cards', (int) $card['index'], 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                                <svg class="t2c__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                                    <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                                          stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Animationen: overskriften wipes ind, og kortene toner ind oppe fra højre
    ét ad gangen, når sektionen kommer ind i billedet. Samme mønster som i
    tekst og billede — se den blok for forklaringen. Ikke i editoren.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t2c--armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -30% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
