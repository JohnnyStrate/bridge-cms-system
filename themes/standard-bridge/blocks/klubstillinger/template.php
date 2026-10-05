<?php
/**
 * Template for "Mesterpoint og klubstillinger".
 *
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, mixed>> $cards  index, image, title, iconColor, iconSvg, text, buttonLabel, href
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block sbklub"<?= eAttr(['style' => $cssVars]) ?>>
    <header class="sbklub__head">
        <h2 class="sbklub__title"><span<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></span></h2>

        <?php if ($text !== '' || $editing): ?>
            <p class="sbklub__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($cards !== []): ?>
        <div class="sbklub__grid">
            <?php foreach ($cards as $n => $card): ?>
                <?php
                // I editoren kobles billedet til sin række i panelet.
                $imageEdit = $editing
                    ? ' data-inline-repeater="cards" data-inline-row="' . (int) $card['index'] . '"'
                        . ' data-inline-image="image" title="Klik for at skifte billede"'
                    : '';
                ?>
                <!-- --i er 0 eller 1: kortets plads i rækken, så de to kort kommer efter hinanden. -->
                <article class="sbklub__card" style="--i:<?= (int) $n % 2 ?>">
                    <div class="sbklub__band">
                        <span class="sbklub__image" role="presentation"
                              <?= $card['image'] !== '' ? 'style="background-image:url(\'' . e($card['image']) . '\')"' : '' ?><?= $imageEdit ?>></span>
                        <h3 class="sbklub__card-title"<?= $context->inlineRow('cards', (int) $card['index'], 'title', 'Titel') ?>><?= e($card['title']) ?></h3>
                    </div>

                    <div class="sbklub__body">
                        <?php if ($card['iconSvg'] !== ''): ?>
                            <svg class="sbklub__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"
                                 style="color:<?= e($card['iconColor']) ?>"><?= $card['iconSvg'] ?></svg>
                        <?php endif; ?>

                        <p class="sbklub__card-text"<?= $context->inlineRow('cards', (int) $card['index'], 'text', 'Tekst') ?>><?= e($card['text']) ?></p>

                        <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                            <a class="sbklub__btn" href="<?= e($card['href']) ?>">
                                <span<?= $context->inlineRow('cards', (int) $card['index'], 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                                <svg class="sbklub__arrow" viewBox="0 0 36 12" aria-hidden="true" focusable="false">
                                    <path d="M0 6h34M29 1.5 34 6l-5 4.5" fill="none" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
    section.classList.add('sbklub--armed');

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    section.querySelectorAll('.sbklub__head, .sbklub__card').forEach(function (element) {
        observer.observe(element);
    });
}());
</script>
<?php endif; ?>
