<?php
/**
 * Template for Bridge Card-blå sektion.
 *
 * @var string        $eyebrow
 * @var string        $title
 * @var string        $text
 * @var string        $buttonLabel
 * @var string        $buttonHref
 * @var string        $bgImage   Færdig URL eller ''.
 * @var string        $shape     Formen, billedet ses igennem.
 * @var string        $suit      Kuløren ved overlinjen.
 * @var string        $cssVars
 * @var RenderContext $context
 *
 * Billedet er ÉN flade med billedet som baggrund, klippet til to kopier af
 * formen (se block.css). Så skifter editoren billede og form live på ét
 * element, og browseren henter billedet én gang.
 */
$editing = $context->isInlineEditing();
?>
<?= BridgeCardKit::stylesheet($context) ?>
<section class="block block--bridgecardtheme-cta"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bccta__photo bc-mask" role="presentation" data-bc-reveal="center"
         <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?><?= BridgeCardKit::live($context, 'shape', $shape) ?><?= $context->inlineImage('bg_image') ?>></div>

    <div class="bccta__content">
        <?php if ($eyebrow !== '' || $editing): ?>
            <p class="bccta__eyebrow" data-bc-reveal="left" style="--i:2">
                <span<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></span>
                <span class="bc-icon bc-mask" aria-hidden="true"<?= BridgeCardKit::live($context, 'suit', $suit) ?>></span>
            </p>
        <?php endif; ?>

        <h2 class="bccta__title" data-bc-reveal="left" style="--i:3"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>

        <?php if ($text !== '' || $editing): ?>
            <p class="bccta__text" data-bc-reveal="left" style="--i:4"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="bccta__button" href="<?= e($buttonHref) ?>" data-bc-reveal="bottom" style="--i:5"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></a>
        <?php endif; ?>
    </div>
</section>
<?= BridgeCardKit::revealScript($context) ?>
