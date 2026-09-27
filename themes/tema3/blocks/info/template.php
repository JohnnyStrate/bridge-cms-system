<?php
/**
 * Template for tema 3's info med billede.
 *
 * @var string                            $title
 * @var string                            $eyebrow
 * @var array<int, string>                $paragraphs  Færdig, sikker HTML.
 * @var string                            $note        Færdig, sikker HTML. Tom = ingen NB-linje.
 * @var string                            $bodyRaw     Rå tekst til editoren.
 * @var string                            $noteRaw     Rå tekst til editoren.
 * @var array<int, array<string, string>> $buttons     field, label, href.
 * @var string                            $image       Færdig, valideret URL.
 * @var string                            $imageAlt
 * @var string                            $cssVars
 * @var RenderContext                     $context
 *
 * I EDITOREN vises brødteksten og NB-linjen rå — med stjernerne synlige —
 * så de kan redigeres direkte uden at miste formateringen.
 */
$editing = $context->isInlineEditing();

// Pilen tegnes én gang og spejles til højre side i CSS'en.
$arrow = '<svg viewBox="0 0 24 32" aria-hidden="true" focusable="false">'
    . '<path d="M5 5 L19 16 L5 27" fill="none" stroke="currentColor" stroke-width="5.5"'
    . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="block block--tema3-info"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t3info__heading">
        <span class="t3info__arrow t3info__arrow--left"><?= $arrow ?></span>

        <div class="t3info__words">
            <h2 class="t3info__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
            <?php if ($eyebrow !== '' || $editing): ?>
                <span class="t3info__eyebrow"<?= $context->inline('eyebrow', 'Minititel') ?>><?= e($eyebrow) ?></span>
            <?php endif; ?>
        </div>

        <span class="t3info__arrow t3info__arrow--right"><?= $arrow ?></span>
    </div>

    <div class="t3info__box">
        <div class="t3info__inner">
            <div class="t3info__text">
                <?php if ($editing): ?>
                    <p class="t3info__body t3info__body--raw"<?= $context->inline('body', 'Tekst — *fremhævet*, **ekstra fed**', true) ?>><?= e($bodyRaw) ?></p>
                    <p class="t3info__note"<?= $context->inline('note', 'NB-linje (tom = ingen)', true) ?>><?= e($noteRaw) ?></p>
                <?php else: ?>
                    <?php foreach ($paragraphs as $paragraph): ?>
                        <p class="t3info__body"><?= $paragraph ?></p>
                    <?php endforeach; ?>
                    <?php if ($note !== ''): ?>
                        <p class="t3info__note"><?= nl2br($note, false) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="t3info__media">
                <?php if ($image !== ''): ?>
                    <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" decoding="async"<?= $context->inlineImage('image') ?>>
                <?php else: ?>
                    <span class="t3info__media-empty"<?= $context->inlineImage('image') ?>><?= $editing ? 'Vælg billede' : '' ?></span>
                <?php endif; ?>

                <?php if ($buttons !== []): ?>
                    <div class="t3info__buttons">
                        <?php foreach ($buttons as $button): ?>
                            <a class="t3info__button" href="<?= e($button['href']) ?>">
                                <span<?= $context->inline($button['field'], 'Knaptekst') ?>><?= e($button['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen:
      1. Pilene starter lige foran hinanden midt på linjen og skubbes ud
         til hver sin side, mens overskriften og minititlen wipes frem
         fra midten.
      2. Boksen glider roligt op, og tekst og billede toner frem.
    Scriptet måler, hvor langt hver pil skal flyttes for at mødes på
    midten, og gemmer det i --t3i-shift. Ikke i editoren, og ikke hvis
    brugeren har slået animationer fra. Virker også i den eksporterede side.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    var heading = section.querySelector('.t3info__heading');
    var arrows = section.querySelectorAll('.t3info__arrow');

    // Hvor langt skal hver pil flyttes for at stå lige foran den anden
    // midt på linjen? offsetLeft måles UDEN den flytning, der allerede er
    // lagt på, så det kan regnes ud igen, når skrifttypen er hentet.
    function measure() {
        if (!heading || arrows.length !== 2) {
            return;
        }
        var middle = heading.clientWidth / 2;
        var gap = arrows[0].offsetWidth * 0.6;
        [[arrows[0], -gap], [arrows[1], gap]].forEach(function (pair) {
            var centre = pair[0].offsetLeft + pair[0].offsetWidth / 2;
            pair[0].style.setProperty('--t3i-shift', (middle + pair[1] - centre) + 'px');
        });
    }

    measure();
    section.classList.add('t3info--armed');

    var image = section.querySelector('.t3info__media img');
    var ready = new Promise(function (resolve) {
        if (!image) {
            resolve();
            return;
        }
        var done = image.decode ? image.decode() : Promise.resolve();
        done.then(resolve, resolve);
        setTimeout(resolve, 2000);
    });
    // Titlens bredde afhænger af skrifttypen — vent på den (højst 2 sek.).
    var fonts = document.fonts && document.fonts.ready
        ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 2000); })])
        : Promise.resolve();
    ready = Promise.all([ready, fonts]);

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                ready.then(function () {
                    measure();
                    // Browseren skal nå at se pilenes startplads, før
                    // animationen går i gang — ellers springer de over den.
                    void heading.getBoundingClientRect();
                    entry.target.classList.add('is-visible');
                });
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
