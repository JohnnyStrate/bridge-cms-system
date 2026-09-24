<?php
/**
 * Template for tema 2's hero.
 *
 * @var string        $title
 * @var string        $text
 * @var string        $buttonLabel  Tom = ingen knap.
 * @var string        $buttonHref
 * @var string        $bgImage      Færdig, valideret URL.
 * @var string        $cssVars
 * @var RenderContext $context
 *
 * Baggrundsbilledet står i style-attributten, fordi det er forskelligt fra
 * blok til blok. Det er sikkert, fordi FieldValidator allerede har afvist
 * alt andet end en simpel filsti — ingen anførselstegn eller parenteser.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--tema2-hero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t2hero__bg" role="presentation"
         <?= $bgImage !== '' ? 'style="background-image:url(\'' . e($bgImage) . '\')"' : '' ?><?= $context->inlineImage('bg_image') ?>></div>
    <div class="t2hero__overlay" aria-hidden="true"></div>

    <div class="t2hero__content">
        <h1 class="t2hero__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>

        <?php if ($text !== '' || $buttonLabel !== '' || $editing): ?>
            <div class="t2hero__row">
                <?php if ($text !== '' || $editing): ?>
                    <p class="t2hero__text"<?= $context->inline('text', 'Tekst', true) ?>><?= $editing ? e($text) : nl2br(e($text)) ?></p>
                <?php endif; ?>

                <?php if ($buttonLabel !== '' || $editing): ?>
                    <a class="t2hero__button" href="<?= e($buttonHref) ?>">
                        <span class="t2hero__button-label"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                        <svg class="t2hero__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                            <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                                  stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php /* Bølgen fyldes med bølgefarven, så den glider over i sektionen under. */ ?>
    <svg class="t2hero__wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 26 C 70 4 170 2 330 12 C 640 32 990 70 1235 86 C 1345 93 1415 84 1440 50 V100 H0 Z"/>
    </svg>
</section>
