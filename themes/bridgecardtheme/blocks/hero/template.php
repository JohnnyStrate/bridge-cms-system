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
 * @var string        $suit         Kuløren bag titlen.
 * @var string        $cssVars
 * @var RenderContext $context
 *
 * Baggrundsbilledet står i style-attributten, fordi det er forskelligt fra
 * blok til blok. Det er sikkert, fordi FieldValidator allerede har afvist
 * alt andet end en simpel filsti.
 */
$editing = $context->isInlineEditing();
?>
<?= BridgeCardKit::stylesheet($context) ?>
<section class="block block--bridgecardtheme-hero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bchero__bg" role="presentation"
         <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?><?= $context->inlineImage('bg_image') ?>></div>

    <div class="bchero__content">
        <span class="bchero__suit bc-mask" aria-hidden="true" data-bc-reveal="center"<?= BridgeCardKit::live($context, 'suit', $suit) ?>></span>

        <h1 class="bchero__title" data-bc-reveal="center" style="--i:1"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>

        <?php if ($text !== '' || $editing): ?>
            <p class="bchero__text" data-bc-reveal="bottom" style="--i:2"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="bchero__button" href="<?= e($buttonHref) ?>" data-bc-reveal="bottom" style="--i:3"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></a>
        <?php endif; ?>
    </div>

    <?php if ($phone !== '' || $address !== '' || $editing): ?>
        <div class="bchero__info" data-bc-reveal="left" style="--i:4">
            <?php if ($phone !== '' || $editing): ?>
                <a class="bchero__info-item" href="<?= e($phoneHref) ?>">
                    <span<?= $context->inline('phone', 'Telefon') ?>><?= e($phone) ?></span>
                    <span class="bc-icon bc-mask" aria-hidden="true" data-value="Telefon"></span>
                </a>
            <?php endif; ?>

            <?php if ($address !== '' || $editing): ?>
                <p class="bchero__info-item">
                    <span<?= $context->inline('address', 'Adresse') ?>><?= e($address) ?></span>
                    <span class="bc-icon bc-mask" aria-hidden="true" data-value="Hus"></span>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?= BridgeCardKit::revealScript($context) ?>
