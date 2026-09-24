<?php
/**
 * Template for tema 2's mørke sektion med svævende overskrift.
 *
 * @var string             $title
 * @var array<int, string> $paragraphs  Færdig, sikker HTML pr. afsnit.
 * @var string             $bodyRaw     Rå tekst til editoren.
 * @var string             $buttonLabel
 * @var string             $buttonHref
 * @var bool               $showSuits
 * @var string             $cssVars
 * @var RenderContext      $context
 *
 * .t2co__bg er den mørke flade. Den er et element for sig (ikke sektionens
 * baggrund), så den kan starte midt på overskriftsboksen og wipes ind.
 */
$editing = $context->isInlineEditing();

$heart = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
    . '<path d="M12 21s-8.5-5.3-8.5-11.1A4.7 4.7 0 0 1 12 7.3a4.7 4.7 0 0 1 8.5 2.6C20.5 15.7 12 21 12 21z"/></svg>';
$spade = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
    . '<path d="M12 2.5s-8 5.8-8 11.1a4.2 4.2 0 0 0 7 3.1L9.8 21.5h4.4L13 16.7a4.2 4.2 0 0 0 7-3.1c0-5.3-8-11.1-8-11.1z"/></svg>';
?>
<section class="block block--tema2-callout"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t2co__bg" aria-hidden="true"></div>

    <h2 class="t2co__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>

    <div class="t2co__content">
        <?php if ($showSuits): ?>
            <span class="t2co__suit t2co__suit--heart"><?= $heart ?></span>
            <span class="t2co__suit t2co__suit--spade"><?= $spade ?></span>
        <?php endif; ?>

        <?php if ($editing): ?>
            <p class="t2co__text"<?= $context->inline('body', 'Tekst — **fed**, *kursiv*', true) ?>><?= e($bodyRaw) ?></p>
        <?php else: ?>
            <?php foreach ($paragraphs as $index => $paragraph): ?>
                <p class="t2co__text" style="--i:<?= (int) $index ?>"><?= $paragraph ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="t2co__button" href="<?= e($buttonHref) ?>" style="--i:<?= count($paragraphs) ?>">
                <span class="t2co__button-label"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                <svg class="t2co__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                    <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                          stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Animationen (ikke i editoren): den mørke flade wipes ind fra venstre,
    overskriftsboksen åbner sig fra midten, og teksten "fokuserer" ind —
    hvert afsnit glider op fra en let sløring, ét ad gangen.
    Samme mønster som i de andre tema 2-sektioner.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t2co--armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
