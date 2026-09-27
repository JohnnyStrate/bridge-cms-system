<?php
/**
 * Template for Bridge Card-heroen.
 *
 * @var string        $title
 * @var string        $text
 * @var string        $bgImage  Færdig, valideret URL.
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
            <p class="bchero__text"<?= $context->inline('text', 'Tekst') ?>><?= e($text) ?></p>
        <?php endif; ?>
    </div>
</section>
