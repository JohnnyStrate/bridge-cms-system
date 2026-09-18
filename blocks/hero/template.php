<?php
/**
 * /**
 * Template for Hero-blokken.
 *
 * @var string        $title
 * @var string        $address
 * @var string        $phone
 * @var string        $bgImage  Færdig, valideret URL.
 * @var string        $cssVars  CSS-variabler til style-attributten.
 * @var RenderContext $context
 * Baggrundsbilledet står bevidst i style-attributten frem for i CSS-filen,
 * fordi værdien er dynamisk pr. blok. Det er sikkert her, fordi
 * FieldValidator::imagePath() allerede har afvist alt, der ikke er en
 * simpel filsti — ingen anførselstegn, ingen parenteser, ingen '..'.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--hero"<?= eAttr(['style' => $cssVars]) ?>>
    <?php if ($bgImage !== '' || $editing): ?>
        <div class="hero__background"
             <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?>
             role="presentation"<?= $context->inlineImage('bg_image') ?>></div>
    <?php endif; ?>

    <div class="hero__panel">
        <h1 class="hero__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>

        <?php if ($address !== '' || $phone !== '' || $editing): ?>
            <p class="hero__meta">
                <?php if ($address !== '' || $editing): ?>
                    <span class="hero__address"<?= $context->inline('address', 'Adresse') ?>><?= e($address) ?></span>
                <?php endif; ?>

                <?php if ($phone !== '' || $editing): ?>
                    <a class="hero__phone" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"<?= $context->inline('phone', 'Telefon') ?>><?= e($phone) ?></a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>