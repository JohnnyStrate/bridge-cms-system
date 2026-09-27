<?php
/**
 * Template for tema 3's linkkort.
 *
 * @var string                           $title
 * @var string                           $eyebrow
 * @var array<int, array<string, mixed>> $cards    index, title, text, buttonLabel, href.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

// Samme pil som i de andre tema 3-sektioner — spejles til højre i CSS'en.
$arrow = '<svg viewBox="0 0 24 32" aria-hidden="true" focusable="false">'
    . '<path d="M5 5 L19 16 L5 27" fill="none" stroke="currentColor" stroke-width="5.5"'
    . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section class="block block--tema3-links"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t3lk__heading">
        <span class="t3lk__arrow t3lk__arrow--left"><?= $arrow ?></span>

        <div class="t3lk__words">
            <h2 class="t3lk__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
            <?php if ($eyebrow !== '' || $editing): ?>
                <span class="t3lk__eyebrow"<?= $context->inline('eyebrow', 'Minititel') ?>><?= e($eyebrow) ?></span>
            <?php endif; ?>
        </div>

        <span class="t3lk__arrow t3lk__arrow--right"><?= $arrow ?></span>
    </div>

    <?php if ($cards !== []): ?>
        <ul class="t3lk__cards">
            <?php foreach ($cards as $n => $card): ?>
                <?php $i = (int) $card['index']; ?>
                <li class="t3lk__card" style="--i:<?= (int) $n ?>">
                    <h3 class="t3lk__card-title"<?= $context->inlineRow('cards', $i, 'title', 'Overskrift') ?>><?= e($card['title']) ?></h3>
                    <p class="t3lk__card-text"<?= $context->inlineRow('cards', $i, 'text', 'Tekst') ?>><?= e($card['text']) ?></p>

                    <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                        <a class="t3lk__button" href="<?= e($card['href']) ?>">
                            <span<?= $context->inlineRow('cards', $i, 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: titellinjen som i de andre tema 3-sektioner (pilene mødes
    på midten og skubbes ud, ordene wipes frem), derefter stiger kortene
    roligt op ét ad gangen. Ikke i editoren, og ikke hvis brugeren har
    slået animationer fra.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    var heading = section.querySelector('.t3lk__heading');
    var arrows = section.querySelectorAll('.t3lk__arrow');

    function measure() {
        if (!heading || arrows.length !== 2) {
            return;
        }
        var middle = heading.clientWidth / 2;
        var gap = arrows[0].offsetWidth * 0.6;
        [[arrows[0], -gap], [arrows[1], gap]].forEach(function (pair) {
            var centre = pair[0].offsetLeft + pair[0].offsetWidth / 2;
            pair[0].style.setProperty('--t3l-shift', (middle + pair[1] - centre) + 'px');
        });
    }

    measure();
    section.classList.add('t3lk--armed');

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
