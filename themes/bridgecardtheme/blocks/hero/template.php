<?php
/**
 * Template for Bridge Card-heroen.
 *
 * @var string        $title
 * @var string        $text
 * @var string        $buttonLabel  Tom = ingen knap.
 * @var string        $buttonHref
 * @var string        $phone
 * @var string        $phoneHref
 * @var string        $address
 * @var string        $bgImage      Færdig, valideret URL.
 * @var string        $cssVars
 * @var RenderContext $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--bridgecardtheme-hero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bchero__bg" role="presentation"
         <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?><?= $context->inlineImage('bg_image') ?>></div>

    <div class="bchero__content">
        <h1 class="bchero__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>

        <?php if ($text !== '' || $editing): ?>
            <p class="bchero__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="bchero__button" href="<?= e($buttonHref) ?>"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></a>
        <?php endif; ?>
    </div>

    <?php if ($phone !== '' || $address !== '' || $editing): ?>
        <div class="bchero__info">
            <?php if ($phone !== '' || $editing): ?>
                <a class="bchero__info-item" href="<?= e($phoneHref) ?>">
                    <span<?= $context->inline('phone', 'Telefon') ?>><?= e($phone) ?></span>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= BridgeCardKit::PHONE ?></svg>
                </a>
            <?php endif; ?>

            <?php if ($address !== '' || $editing): ?>
                <p class="bchero__info-item">
                    <span<?= $context->inline('address', 'Adresse') ?>><?= e($address) ?></span>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= BridgeCardKit::HOUSE ?></svg>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
