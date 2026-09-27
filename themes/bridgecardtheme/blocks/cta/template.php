<?php
/**
 * Template for Bridge Card-blå sektion.
 *
 * @var string        $eyebrow
 * @var string        $title
 * @var string        $text
 * @var string        $buttonLabel
 * @var string        $buttonHref
 * @var string        $imageLeft   Færdig URL eller ''.
 * @var string        $imageRight
 * @var string        $cssVars
 * @var RenderContext $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--bridgecardtheme-cta"<?= eAttr(['style' => $cssVars]) ?>>
    <?php if ($imageLeft !== '' || $editing): ?>
        <div class="bccta__image bccta__image--left"<?= $context->inlineImage('image_left') ?>>
            <?php if ($imageLeft !== ''): ?><img src="<?= e($imageLeft) ?>" alt=""><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($imageRight !== '' || $editing): ?>
        <div class="bccta__image bccta__image--right"<?= $context->inlineImage('image_right') ?>>
            <?php if ($imageRight !== ''): ?><img src="<?= e($imageRight) ?>" alt=""><?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="bccta__content">
        <?php if ($eyebrow !== '' || $editing): ?>
            <p class="bccta__eyebrow">
                <span<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></span>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= BridgeCardKit::CLUB ?></svg>
            </p>
        <?php endif; ?>

        <h2 class="bccta__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>

        <?php if ($text !== '' || $editing): ?>
            <p class="bccta__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="bccta__button" href="<?= e($buttonHref) ?>"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></a>
        <?php endif; ?>
    </div>
</section>
