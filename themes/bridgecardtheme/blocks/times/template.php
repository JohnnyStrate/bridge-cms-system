<?php
/**
 * Template for Bridge Card-spilletider.
 *
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, mixed>> $items  index, label, value
 * @var string                           $image  Færdig URL eller ''.
 * @var string                           $imageAlt
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--bridgecardtheme-times"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bctimes__inner">
        <div class="bctimes__content">
            <svg class="bctimes__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= BridgeCardKit::CLUB ?></svg>

            <h2 class="bctimes__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>

            <?php if ($text !== '' || $editing): ?>
                <p class="bctimes__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
            <?php endif; ?>

            <?php if ($items !== []): ?>
                <ul class="bctimes__items">
                    <?php foreach ($items as $item): ?>
                        <li class="bctimes__item">
                            <span class="bctimes__label"<?= $context->inlineRow('items', (int) $item['index'], 'label', 'Lille overskrift') ?>><?= e($item['label']) ?></span>
                            <span class="bctimes__value"<?= $context->inlineRow('items', (int) $item['index'], 'value', 'Tekst') ?>><?= e($item['value']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if ($image !== '' || $editing): ?>
            <div class="bctimes__media"<?= $context->inlineImage('image') ?>>
                <?php if ($image !== ''): ?>
                    <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" loading="lazy">
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
