<?php
/**
 * Template for "Turneringer og resultater".
 *
 * @var string                           $title
 * @var array<int, array<string, mixed>> $cards  index, image, title, text, buttonLabel, href
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block sbtur"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="sbtur__inner">
        <h2 class="sbtur__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>

        <?php if ($cards !== []): ?>
            <div class="sbtur__grid">
                <?php foreach ($cards as $n => $card): ?>
                    <?php
                    // I editoren kobles billedet til sin række i panelet.
                    $imageEdit = $editing
                        ? ' data-inline-repeater="cards" data-inline-row="' . (int) $card['index'] . '"'
                            . ' data-inline-image="image" title="Klik for at skifte billede"'
                        : '';
                    ?>
                    <article class="sbtur__card" style="--i:<?= (int) $n % 2 ?>">
                        <a class="sbtur__media" href="<?= e($card['href']) ?>">
                            <span class="sbtur__image" role="presentation"
                                  <?= $card['image'] !== '' ? 'style="background-image:url(\'' . e($card['image']) . '\')"' : '' ?><?= $imageEdit ?>></span>

                            <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                                <span class="sbtur__overlay">
                                    <span class="sbtur__button">
                                        <span<?= $context->inlineRow('cards', (int) $card['index'], 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                                        <svg class="sbtur__arrow" viewBox="0 0 48 12" aria-hidden="true" focusable="false">
                                            <path d="M0 6h46M41 1.5 46 6l-5 4.5" fill="none" stroke="currentColor" stroke-width="1"/>
                                        </svg>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </a>

                        <h3 class="sbtur__card-title">
                            <span<?= $context->inlineRow('cards', (int) $card['index'], 'title', 'Titel') ?>><?= e($card['title']) ?></span>
                            <svg viewBox="0 0 12 12" aria-hidden="true" focusable="false">
                                <path d="M3 9l6-6M4.5 3H9v4.5" fill="none" stroke="currentColor" stroke-width="1.2"/>
                            </svg>
                        </h3>
                        <p class="sbtur__card-text"<?= $context->inlineRow('cards', (int) $card['index'], 'text', 'Tekst') ?>><?= e($card['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (!$editing): ?>
<?php /* Starter animationerne, når overskriften og hvert kort ruller ind på skærmen. Ikke i editoren. */ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('sbtur--armed');

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    section.querySelectorAll('.sbtur__title, .sbtur__card').forEach(function (element) {
        observer.observe(element);
    });
}());
</script>
<?php endif; ?>
