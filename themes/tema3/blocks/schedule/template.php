<?php
/**
 * Template for tema 3's tider og steder.
 *
 * @var string                           $title
 * @var string                           $eyebrow
 * @var array<int, array<string, mixed>> $items    index, title, text, linkLabel, href.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

// Samme pil som i "Info med billede" — spejles til højre side i CSS'en.
$arrow = '<svg viewBox="0 0 24 32" aria-hidden="true" focusable="false">'
    . '<path d="M5 5 L19 16 L5 27" fill="none" stroke="currentColor" stroke-width="5.5"'
    . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="block block--tema3-schedule"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t3sch__heading">
        <span class="t3sch__arrow t3sch__arrow--left"><?= $arrow ?></span>

        <div class="t3sch__words">
            <h2 class="t3sch__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
            <?php if ($eyebrow !== '' || $editing): ?>
                <span class="t3sch__eyebrow"<?= $context->inline('eyebrow', 'Minititel') ?>><?= e($eyebrow) ?></span>
            <?php endif; ?>
        </div>

        <span class="t3sch__arrow t3sch__arrow--right"><?= $arrow ?></span>
    </div>

    <?php if ($items !== []): ?>
        <ul class="t3sch__list">
            <?php foreach ($items as $n => $item): ?>
                <?php $i = (int) $item['index']; ?>
                <li class="t3sch__item" style="--i:<?= (int) $n ?>">
                    <h3 class="t3sch__item-title"<?= $context->inlineRow('items', $i, 'title', 'Overskrift') ?>><?= e($item['title']) ?></h3>

                    <p class="t3sch__box"<?= $context->inlineRow('items', $i, 'text', 'Tekst i boksen') ?>><?= e($item['text']) ?></p>

                    <?php if ($item['linkLabel'] !== '' || $editing): ?>
                        <a class="t3sch__link" href="<?= e($item['href']) ?>">
                            <span class="t3sch__link-label"<?= $context->inlineRow('items', $i, 'link_label', 'Linktekst') ?>><?= e($item['linkLabel']) ?></span>
                            <span class="t3sch__line" aria-hidden="true"></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen:
      1. Titellinjen: pilene mødes på midten og skubbes ud, mens ordene
         wipes frem — samme som "Info med billede", så de to sektioner
         hænger sammen.
      2. Punkterne toner roligt frem ét ad gangen: overskriften glider ind
         fra venstre, boksen fra højre.
    Ikke i editoren, og ikke hvis brugeren har slået animationer fra.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    var heading = section.querySelector('.t3sch__heading');
    var arrows = section.querySelectorAll('.t3sch__arrow');

    // Hvor langt skal hver pil flyttes for at stå lige foran den anden
    // midt på linjen? offsetLeft måles uden den flytning, der er lagt på.
    function measure() {
        if (!heading || arrows.length !== 2) {
            return;
        }
        var middle = heading.clientWidth / 2;
        var gap = arrows[0].offsetWidth * 0.6;
        [[arrows[0], -gap], [arrows[1], gap]].forEach(function (pair) {
            var centre = pair[0].offsetLeft + pair[0].offsetWidth / 2;
            pair[0].style.setProperty('--t3s-shift', (middle + pair[1] - centre) + 'px');
        });
    }

    measure();
    section.classList.add('t3sch--armed');

    // Titlens bredde afhænger af skrifttypen — vent på den (højst 2 sek.).
    var fonts = document.fonts && document.fonts.ready
        ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 2000); })])
        : Promise.resolve();

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                fonts.then(function () {
                    measure();
                    void heading.getBoundingClientRect();
                    entry.target.classList.add('is-visible');
                });
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
