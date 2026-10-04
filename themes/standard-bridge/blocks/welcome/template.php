<?php
/**
 * Template for standardbridge-welcome.
 *
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, mixed>> $cards  index, image, title, text, buttonLabel, href
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block sbwel"<?= eAttr(['style' => $cssVars]) ?>>
    <header class="sbwel__head">
        <h2 class="sbwel__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>

        <?php if ($text !== '' || $editing): ?>
            <p class="sbwel__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($cards !== []): ?>
        <div class="sbwel__grid">
            <?php foreach ($cards as $n => $card): ?>
                <?php
                // I editoren kobles billedet til sin række i panelet, så et
                // klik på det åbner filvælgeren, og et nyt billede vises live.
                $imageEdit = $editing
                    ? ' data-inline-repeater="cards" data-inline-row="' . (int) $card['index'] . '"'
                        . ' data-inline-image="image" title="Klik for at skifte billede"'
                    : '';
                ?>
                <article class="sbwel__card" style="--i:<?= (int) $n ?>">
                    <a class="sbwel__media" href="<?= e($card['href']) ?>">
                        <span class="sbwel__image" role="presentation"
                              <?= $card['image'] !== '' ? 'style="background-image:url(\'' . e($card['image']) . '\')"' : '' ?><?= $imageEdit ?>></span>

                        <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                            <span class="sbwel__overlay">
                                <span class="sbwel__button">
                                    <span<?= $context->inlineRow('cards', (int) $card['index'], 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                                    <svg class="sbwel__arrow" viewBox="0 0 48 12" aria-hidden="true" focusable="false">
                                        <path d="M0 6h46M41 1.5 46 6l-5 4.5" fill="none" stroke="currentColor" stroke-width="1"/>
                                    </svg>
                                </span>
                            </span>
                        <?php endif; ?>
                    </a>

                    <h3 class="sbwel__card-title"<?= $context->inlineRow('cards', (int) $card['index'], 'title', 'Titel') ?>><?= e($card['title']) ?></h3>
                    <p class="sbwel__card-text"<?= $context->inlineRow('cards', (int) $card['index'], 'text', 'Tekst') ?>><?= e($card['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /*
    Starter animationerne, når tingene ruller ind på skærmen. Overskriften
    og hvert kort holdes øje med hver for sig, så et kort først kommer ind,
    når man faktisk kan se det. .sbwel--armed skjuler, .is-visible viser.
    Uden JavaScript, i editoren og ved "reducer bevægelse" er alt synligt.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('sbwel--armed');

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    section.querySelectorAll('.sbwel__head, .sbwel__card').forEach(function (element) {
        observer.observe(element);
    });
}());
</script>
<?php endif; ?>
