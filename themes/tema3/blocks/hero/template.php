<?php
/**
 * Template for tema 3's hero.
 *
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, string>> $buttons  field, label, href.
 * @var string                           $image     Færdig, valideret URL.
 * @var string                           $imageAlt
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--tema3-hero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t3hero__media">
        <?php if ($image !== ''): ?>
            <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" decoding="async"<?= $context->inlineImage('image') ?>>
        <?php else: ?>
            <span class="t3hero__media-empty"<?= $context->inlineImage('image') ?>><?= $editing ? 'Vælg billede' : '' ?></span>
        <?php endif; ?>
    </div>

    <h1 class="t3hero__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>

    <div class="t3hero__bottom">
        <?php if ($text !== '' || $editing): ?>
            <p class="t3hero__text"<?= $context->inline('text', 'Tekst', true) ?>><?= $editing ? e($text) : nl2br(e($text)) ?></p>
        <?php endif; ?>

        <?php if ($buttons !== []): ?>
            <div class="t3hero__buttons">
                <?php foreach ($buttons as $button): ?>
                    <a class="t3hero__button" href="<?= e($button['href']) ?>">
                        <span class="t3hero__button-label"<?= $context->inline($button['field'], 'Knaptekst') ?>><?= e($button['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: billedet swiper ind fra venstre, teksten og knapperne fra
    højre. Sektionen skjules ("armed") af scriptet, der står lige efter
    den, så den aldrig blinker. Ikke i editoren, og ikke hvis brugeren har
    slået animationer fra. Virker også i den eksporterede side.

    Den venter på billedet (højst 2 sekunder), så man ikke ser en tom
    flade komme ind.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t3hero--armed');
    var image = section.querySelector('.t3hero__media img');
    var ready = new Promise(function (resolve) {
        if (!image) {
            resolve();
            return;
        }
        var done = image.decode ? image.decode() : Promise.resolve();
        done.then(resolve, resolve);
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
    }, { rootMargin: '0px 0px -15% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
