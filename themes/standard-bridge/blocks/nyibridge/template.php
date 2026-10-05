<?php
/**
 * Template for "Ny i bridge".
 *
 * @var string                           $title
 * @var string                           $image  Færdig URL eller ''.
 * @var array<int, array<string, mixed>> $items  index, label, text
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block sbny"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="sbny__banner">
        <span class="sbny__image" role="presentation"
              <?= $image !== '' ? 'style="background-image:url(\'' . e($image) . '\')"' : '' ?><?= $context->inlineImage('image') ?>></span>
        <h2 class="sbny__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>
    </div>

    <?php if ($items !== []): ?>
        <div class="sbny__grid">
            <?php foreach ($items as $n => $item): ?>
                <div class="sbny__item" style="--i:<?= (int) $n ?>">
                    <h3 class="sbny__label">
                        <span<?= $context->inlineRow('items', (int) $item['index'], 'label', 'Lille overskrift') ?>><?= e($item['label']) ?></span>
                    </h3>
                    <p class="sbny__value"<?= $context->inlineRow('items', (int) $item['index'], 'text', 'Tekst') ?>><?= e($item['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /* Starter animationerne, når båndet og oplysningerne ruller ind på skærmen. Ikke i editoren. */ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('sbny--armed');

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    section.querySelectorAll('.sbny__banner, .sbny__grid').forEach(function (element) {
        observer.observe(element);
    });
}());
</script>
<?php endif; ?>
