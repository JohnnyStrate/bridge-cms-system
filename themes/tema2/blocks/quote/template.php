<?php
/**
 * Template for tema 2's citat med billede.
 *
 * @var string             $quote       Uden citattegn — de sættes på her.
 * @var array<int, string> $words       Citatets ord, til animationen.
 * @var string             $text
 * @var string             $buttonLabel
 * @var string             $buttonHref
 * @var string             $image       Færdig, valideret URL.
 * @var string             $imageAlt
 * @var bool               $imageRight
 * @var bool               $showSuit
 * @var string             $cssVars
 * @var RenderContext      $context
 *
 * I editoren står citatet som én redigerbar tekst. På siden står hvert ord i
 * sin egen <span>, så ordene kan toner ind efter hinanden.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--tema2-quote<?= $imageRight ? ' t2q--image-right' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t2q__bg" aria-hidden="true"></div>

    <div class="t2q__card">
        <?php if ($image !== '' || $editing): ?>
            <div class="t2q__media">
                <?php if ($image !== ''): ?>
                    <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" decoding="async"<?= $context->inlineImage('image') ?>>
                <?php else: ?>
                    <span class="t2q__media-empty"<?= $context->inlineImage('image') ?>>Vælg billede</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="t2q__text">
            <blockquote class="t2q__quote">
                <?php if ($editing): ?>
                    <p>&ldquo;<span<?= $context->inline('quote', 'Citat', true) ?>><?= e($quote) ?></span>&rdquo;</p>
                <?php else: ?>
                    <p><span class="t2q__sr"><?= e('“' . $quote . '”') ?></span><?php
                        $last = count($words) - 1;
                        foreach ($words as $index => $word):
                            $shown = ($index === 0 ? '“' : '') . $word . ($index === $last ? '”' : '');
                        ?><span class="t2q__word" aria-hidden="true" style="--w:<?= (int) $index ?>"><?= e($shown) ?></span> <?php endforeach; ?></p>
                <?php endif; ?>
            </blockquote>

            <?php if ($text !== '' || $editing): ?>
                <p class="t2q__body"<?= $context->inline('text', 'Tekst', true) ?>><?= $editing ? e($text) : nl2br(e($text)) ?></p>
            <?php endif; ?>

            <?php if ($buttonLabel !== '' || $editing): ?>
                <a class="t2q__button" href="<?= e($buttonHref) ?>">
                    <span class="t2q__button-label"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                    <svg class="t2q__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                        <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($showSuit): ?>
            <svg class="t2q__suit" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M12 2.5s-8 5.8-8 11.1a4.2 4.2 0 0 0 7 3.1L9.8 21.5h4.4L13 16.7a4.2 4.2 0 0 0 7-3.1c0-5.3-8-11.1-8-11.1z"/>
            </svg>
        <?php endif; ?>
    </div>

    <div class="t2q__spacer" aria-hidden="true"></div>
</section>
<?php if (!$editing): ?>
<?php /*
    Animationen (ikke i editoren): kortet glider op, billedet afdækkes
    nedefra, citatet toner ind ord for ord, og til sidst tekst, knap og
    spar. Venter på billedet ligesom "Tekst og billede".
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t2q--armed');
    var image = section.querySelector('.t2q__media img');
    var ready = new Promise(function (resolve) {
        if (!image) {
            resolve();
            return;
        }
        (image.decode ? image.decode() : Promise.resolve()).then(resolve, resolve);
        setTimeout(resolve, 2000);
    });
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                ready.then(function () {
                    entry.target.classList.add('is-visible');
                });
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
