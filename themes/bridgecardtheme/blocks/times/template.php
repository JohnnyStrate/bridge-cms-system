<?php
/**
 * Template for Bridge Card-spilletider.
 *
 * @var string                           $title   Linjeskift bevares.
 * @var string                           $text
 * @var array<int, array<string, mixed>> $items   index, label, value
 * @var string                           $image   Færdig URL eller ''.
 * @var string                           $imageAlt
 * @var string                           $suit
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * Billedet er altid et <img> i editoren (skjult, hvis tomt), så editoren
 * kan skifte det live efter upload.
 */
$editing = $context->isInlineEditing();
?>
<?= BridgeCardKit::stylesheet($context) ?>
<section class="block block--bridgecardtheme-times"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bctimes__inner">
        <div class="bctimes__content">
            <span class="bctimes__suit bc-icon bc-mask" aria-hidden="true" data-bc-reveal="top"<?= BridgeCardKit::live($context, 'suit', $suit) ?>></span>

            <h2 class="bctimes__title" data-bc-reveal="left" style="--i:1"<?= $context->inline('title', 'Overskrift', true) ?>><?= e($title) ?></h2>

            <?php if ($text !== '' || $editing): ?>
                <p class="bctimes__text" data-bc-reveal="left" style="--i:2"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
            <?php endif; ?>

            <?php if ($items !== []): ?>
                <ul class="bctimes__items">
                    <?php foreach ($items as $n => $item): ?>
                        <li class="bctimes__item" data-bc-reveal="left" style="--i:<?= 3 + (int) $n ?>">
                            <span class="bctimes__label"<?= $context->inlineRow('items', (int) $item['index'], 'label', 'Lille overskrift') ?>><?= e((string) ($item['label'] ?? '')) ?></span>
                            <span class="bctimes__value"<?= $context->inlineRow('items', (int) $item['index'], 'value', 'Tekst') ?>><?= e((string) ($item['value'] ?? '')) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if ($image !== '' || $editing): ?>
            <div class="bctimes__media" data-bc-reveal="right" style="--i:2">
                <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" loading="lazy" decoding="async"<?= $image === '' ? ' hidden' : '' ?><?= $context->inlineImage('image') ?>>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= BridgeCardKit::revealScript($context) ?>
