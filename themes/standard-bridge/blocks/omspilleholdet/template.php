<?php
/**
 * Template for "Om spilleholdet".
 *
 * @var string                           $title
 * @var array<int, array<string, mixed>> $items  index, label, value
 * @var string                           $linkLabel  Tom = intet link.
 * @var string                           $linkHref
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block sbom"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="sbom__inner">
        <h2 class="sbom__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>

        <div class="sbom__box">
            <?php if ($items !== []): ?>
                <dl class="sbom__grid">
                    <?php foreach ($items as $n => $item): ?>
                        <div class="sbom__item" style="--i:<?= (int) $n ?>">
                            <dt class="sbom__label"<?= $context->inlineRow('items', (int) $item['index'], 'label', 'Lille overskrift') ?>><?= e($item['label']) ?></dt>
                            <dd class="sbom__value"<?= $context->inlineRow('items', (int) $item['index'], 'value', 'Tekst') ?>><?= e($item['value']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <?php if ($linkLabel !== '' || $editing): ?>
                <a class="sbom__link" href="<?= e($linkHref) ?>"<?= $context->inline('link_label', 'Link') ?>><?= e($linkLabel) ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if (!$editing): ?>
<?php /* Starter animationen, når sektionen ruller ind på skærmen. Ikke i editoren. */ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('sbom--armed');

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
