<?php
/**
 * Template for tema 2's tekst og billede.
 *
 * @var string             $eyebrow
 * @var string             $title
 * @var array<int, string> $paragraphs  Færdig, sikker HTML (escaped + <strong>/<em>).
 * @var string             $note        Færdig, sikker HTML. Tom = ingen NB-linje.
 * @var string             $bodyRaw     Rå tekst til editoren.
 * @var string             $noteRaw     Rå tekst til editoren.
 * @var string             $buttonLabel
 * @var string             $buttonHref
 * @var string             $image       Færdig, valideret URL.
 * @var string             $imageAlt
 * @var bool               $imageLeft
 * @var bool               $showSuits
 * @var string             $cssVars
 * @var RenderContext      $context
 *
 * I EDITOREN vises brødteksten og NB-linjen som rå tekst med **stjernerne**
 * synlige, så de kan redigeres direkte uden at miste formateringen. På siden
 * vises de formaterede afsnit.
 */
$editing = $context->isInlineEditing();

// Kortikonerne er ren pynt og skjules for skærmlæsere.
$club = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
    . '<circle cx="12" cy="7" r="4.7"/><circle cx="6.6" cy="13.8" r="4.7"/>'
    . '<circle cx="17.4" cy="13.8" r="4.7"/><path d="M12 11.5 10 22.5h4z"/></svg>';
$diamond = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
    . '<rect x="5" y="3" width="14" height="18" rx="2.5"/></svg>';
?>
<section class="block block--tema2-textimage<?= $imageLeft ? ' t2ti--image-left' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t2ti__inner">

        <div class="t2ti__text">
            <?php if ($eyebrow !== '' || $editing): ?>
                <p class="t2ti__eyebrow"<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></p>
            <?php endif; ?>

            <h2 class="t2ti__title">
                <span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span><?php if ($showSuits): ?>&#8288;<span class="t2ti__suit t2ti__suit--title"><?= $club ?></span><?php endif; ?>
            </h2>

            <?php if ($editing): ?>
                <p class="t2ti__body t2ti__body--raw"<?= $context->inline('body', 'Tekst — **fed**, *kursiv*', true) ?>><?= e($bodyRaw) ?></p>
                <p class="t2ti__note"<?= $context->inline('note', 'NB-linje (tom = ingen)', true) ?>><?= e($noteRaw) ?></p>
            <?php else: ?>
                <?php foreach ($paragraphs as $paragraph): ?>
                    <p class="t2ti__body"><?= $paragraph ?></p>
                <?php endforeach; ?>

                <?php if ($note !== ''): ?>
                    <p class="t2ti__note"><?= $note ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($buttonLabel !== '' || $editing): ?>
                <a class="t2ti__button" href="<?= e($buttonHref) ?>">
                    <span class="t2ti__button-label"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                    <svg class="t2ti__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                        <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                              stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($image !== '' || $editing): ?>
            <div class="t2ti__media">
                <div class="t2ti__blob">
                    <?php if ($image !== ''): ?>
                        <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" loading="lazy"<?= $context->inlineImage('image') ?>>
                    <?php else: ?>
                        <span class="t2ti__blob-empty"<?= $context->inlineImage('image') ?>>Vælg billede</span>
                    <?php endif; ?>
                </div>

                <?php if ($showSuits): ?>
                    <span class="t2ti__suit t2ti__suit--diamond"><?= $diamond ?></span>
                    <span class="t2ti__suit t2ti__suit--club"><?= $club ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Wipe-animationen: sektionen skjules ("armed") og vises med en wipe, når
    den kommer ind i billedet. Scriptet står direkte efter sektionen, så det
    kører, før siden tegnes første gang — ellers ville den blinke.
    Ikke i editoren: dér skal alt kunne ses og klikkes på med det samme.
    Virker også i den eksporterede statiske side.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t2ti--armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    // Starter først, når sektionens top er nået 30 % op over bunden af
    // skærmen — ellers kører den, mens man ikke kigger.
    }, { rootMargin: '0px 0px -30% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
