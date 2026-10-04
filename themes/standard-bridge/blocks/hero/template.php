<?php
/**
 * Template for standardbridge-hero.
 *
 * @var string        $title      Linjeskift = ny linje i overskriften.
 * @var string        $tagline
 * @var string        $btn1Label  Tom = ingen knap.
 * @var string        $btn1Href
 * @var string        $btn2Label  Tom = ingen knap.
 * @var string        $btn2Href
 * @var string        $bgImage    Færdig URL eller ''.
 * @var string        $cssVars
 * @var RenderContext $context
 */
$editing = $context->isInlineEditing();

// Hver linje i overskriften får sin egen <span>, så linjerne kan komme ind
// én ad gangen. I editoren er overskriften ét felt, man kan skrive i.
$lines = preg_split('/\R/', $title) ?: [];
?>
<section class="block sbhero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="sbhero__bg" role="presentation"
         <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?><?= $context->inlineImage('bg_image') ?>></div>

    <div class="sbhero__content">
        <?php if ($editing): ?>
            <h1 class="sbhero__title"<?= $context->inline('title', 'Overskrift', true) ?>><?= e($title) ?></h1>
        <?php else: ?>
            <h1 class="sbhero__title">
                <?php foreach ($lines as $i => $line): ?>
                    <span class="sbhero__line"><span style="--i:<?= (int) $i ?>"><?= e($line) ?></span></span>
                <?php endforeach; ?>
            </h1>
        <?php endif; ?>

        <?php if ($tagline !== '' || $editing): ?>
            <p class="sbhero__tagline"<?= $context->inline('tagline', 'Lille tekst') ?>><?= e($tagline) ?></p>
        <?php endif; ?>

        <div class="sbhero__actions">
            <?php if ($btn1Label !== '' || $editing): ?>
                <a class="sbhero__btn sbhero__btn--ghost" href="<?= e($btn1Href) ?>">
                    <span<?= $context->inline('btn1_label', 'Knap 1') ?>><?= e($btn1Label) ?></span>
                </a>
            <?php endif; ?>

            <?php if ($btn2Label !== '' || $editing): ?>
                <a class="sbhero__btn sbhero__btn--solid" href="<?= e($btn2Href) ?>">
                    <span<?= $context->inline('btn2_label', 'Knap 2') ?>><?= e($btn2Label) ?></span>
                    <svg class="sbhero__arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 12h17M14 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Starter animationen, når heroen er på skærmen. Scriptet sætter først
    .sbhero--armed (alt er skjult) og derefter .is-visible (alt kommer ind).
    Uden JavaScript, i editoren og ved "reducer bevægelse" er alt bare synligt.
    Den venter på billedet (højst 1,5 sekund), så det ikke popper ind bagefter.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('sbhero--armed');

    var bg    = section.querySelector('.sbhero__bg');
    var match = bg && /url\(["']?(.*?)["']?\)/.exec(bg.style.backgroundImage);
    var ready = new Promise(function (resolve) {
        if (!match) {
            resolve();
            return;
        }
        var image = new Image();
        image.onload = image.onerror = resolve;
        image.src = match[1];
        setTimeout(resolve, 1500);
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
    }).observe(section);
}());
</script>
<?php endif; ?>