<?php
/**
 * Template for tema 3's billedkarrusel.
 *
 * @var array<int, array<string, mixed>> $slides   index, image, alt, caption.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * På siden vises ét billede ad gangen, og scriptet nederst skifter mellem
 * dem. I EDITOREN vises alle billeder og tekster på én gang, så man kan
 * klikke på dem og redigere dem direkte.
 */
$editing = $context->isInlineEditing();
$count   = count($slides);

// Et billede i en repeater-række kobles til rækkens billedfelt i panelet.
$imageAttr = static function (int $row) use ($editing): string {
    return $editing
        ? ' data-inline-repeater="slides" data-inline-row="' . $row . '"'
            . ' data-inline-image="image" title="Klik for at skifte billede"'
        : '';
};

$arrow = static function (string $direction): string {
    $path = $direction === 'prev' ? 'M19 12H5M11 6l-6 6 6 6' : 'M5 12h14M13 6l6 6-6 6';

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
        . '<path d="' . $path . '" fill="none" stroke="currentColor" stroke-width="2"'
        . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
};
?>
<section class="block block--tema3-slider<?= $editing ? ' t3sl--editing' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>
         aria-roledescription="karrusel" aria-label="Billeder">
    <div class="t3sl__box">
        <?php if ($count > 0 && !$editing): ?>
            <span class="t3sl__counter" aria-hidden="true">1</span>
        <?php endif; ?>

        <div class="t3sl__stage">
            <?php foreach ($slides as $n => $slide): ?>
                <figure class="t3sl__slide<?= $n === 0 ? ' is-active' : '' ?>"
                        data-slide="<?= (int) $n ?>"<?= $n > 0 && !$editing ? ' aria-hidden="true"' : '' ?>>
                    <div class="t3sl__diamond">
                        <?php if ($slide['image'] !== ''): ?>
                            <img src="<?= e($slide['image']) ?>" alt="<?= e($slide['alt']) ?>"
                                 decoding="async"<?= $n > 0 && !$editing ? ' loading="lazy"' : '' ?><?= $imageAttr((int) $slide['index']) ?>>
                        <?php else: ?>
                            <span class="t3sl__empty"<?= $imageAttr((int) $slide['index']) ?>>Vælg billede</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($editing): ?>
                        <figcaption class="t3sl__edit-number"><?= (int) $n + 1 ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>

        <?php if ($count > 1 && !$editing): ?>
            <div class="t3sl__nav">
                <button type="button" class="t3sl__arrow" data-dir="-1" aria-label="Forrige billede"><?= $arrow('prev') ?></button>

                <div class="t3sl__dots">
                    <?php for ($n = 0; $n < $count; $n++): ?>
                        <button type="button" class="t3sl__dot<?= $n === 0 ? ' is-active' : '' ?>"
                                data-go="<?= $n ?>" aria-label="Vis billede <?= $n + 1 ?>"
                                <?= $n === 0 ? 'aria-current="true"' : '' ?>></button>
                    <?php endfor; ?>
                </div>

                <button type="button" class="t3sl__arrow" data-dir="1" aria-label="Næste billede"><?= $arrow('next') ?></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="t3sl__captions" aria-live="polite">
        <?php foreach ($slides as $n => $slide): ?>
            <?php if ($editing): ?>
                <p class="t3sl__caption is-active">
                    <span class="t3sl__edit-number"><?= (int) $n + 1 ?></span>
                    <span<?= $context->inlineRow('slides', (int) $slide['index'], 'caption', 'Tekst til billedet') ?> data-inline-multiline><?= e($slide['caption']) ?></span>
                </p>
            <?php else: ?>
                <p class="t3sl__caption<?= $n === 0 ? ' is-active' : '' ?>" data-slide="<?= (int) $n ?>"
                   <?= $n > 0 ? 'hidden' : '' ?>><?= nl2br(e($slide['caption'])) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php if (!$editing && $count > 0): ?>
<?php /*
    Styrer karrusellen: pile, prikker, piletaster og swipe på mobil.
    Tallet i hjørnet, den aktive prik og teksten til højre følger med.
    Uden script vises det første billede — siden virker stadig.

    Indgangen: boksen glider ind fra venstre, diamanten drejer sig på
    plads, og teksten glider ind — når sektionen kommer i syne.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section) {
        return;
    }

    var slides   = section.querySelectorAll('.t3sl__slide');
    var captions = section.querySelectorAll('.t3sl__caption');
    var dots     = section.querySelectorAll('.t3sl__dot');
    var counter  = section.querySelector('.t3sl__counter');
    var current  = 0;

    // Teksterne er skjult med "hidden" uden script. Nu styrer CSS'en dem.
    captions.forEach(function (caption) {
        caption.hidden = false;
    });
    section.classList.add('t3sl--ready');

    function go(index) {
        var count = slides.length;
        var next  = (index + count) % count;
        if (next === current) {
            return;
        }
        // Retningen bestemmer, hvilken vej teksten glider.
        var forward = index > current;
        section.classList.toggle('t3sl--back', !forward);

        [slides, captions].forEach(function (list) {
            list[current].classList.remove('is-active');
            list[current].classList.add('is-leaving');
            list[current].setAttribute('aria-hidden', 'true');
            list[next].classList.remove('is-leaving');
            list[next].classList.add('is-active');
            list[next].removeAttribute('aria-hidden');
        });

        if (dots.length) {
            dots[current].classList.remove('is-active');
            dots[current].removeAttribute('aria-current');
            dots[next].classList.add('is-active');
            dots[next].setAttribute('aria-current', 'true');
        }

        if (counter) {
            counter.textContent = String(next + 1);
            counter.classList.remove('is-ticking');
            void counter.offsetWidth;
            counter.classList.add('is-ticking');
        }

        current = next;
    }

    section.addEventListener('click', function (event) {
        var arrow = event.target.closest('[data-dir]');
        var dot   = event.target.closest('[data-go]');
        if (arrow) {
            go(current + Number(arrow.dataset.dir));
        } else if (dot) {
            go(Number(dot.dataset.go));
        }
    });

    section.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            go(current - 1);
        } else if (event.key === 'ArrowRight') {
            go(current + 1);
        }
    });

    // Swipe på mobil.
    var startX = null;
    var stage  = section.querySelector('.t3sl__stage');
    stage.addEventListener('touchstart', function (event) {
        startX = event.touches[0].clientX;
    }, { passive: true });
    stage.addEventListener('touchend', function (event) {
        if (startX === null) {
            return;
        }
        var distance = event.changedTouches[0].clientX - startX;
        if (Math.abs(distance) > 40) {
            go(current + (distance < 0 ? 1 : -1));
        }
        startX = null;
    });

    // Indgangen — kun hvis brugeren ikke har slået animationer fra.
    if (!('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('t3sl--armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                entry.target.classList.add('is-visible');
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
