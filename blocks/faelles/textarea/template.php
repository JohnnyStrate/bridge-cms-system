<?php
/**
 * Template for TextArea-blokken.
 *
 * @var string $title
 * @var string $body
 * @var string $cssVars
 * @var RenderContext
 *
 * e() foer nl2br(): escaping foerst, linjeskift bagefter. Bytter man om,
 * bliver de indsatte br-tags selv escapet og vist som synlig tekst.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--textarea"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="textarea__inner">
        <?php if ($title !== ''): ?>
            <h2 class="textarea__title" <?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
        <?php endif; ?>

        <?php if ($body !== ''): ?>
            <div class="textarea__body" <?= $context->inline('body','Tekst' ) ?>><?= nl2br(e($body)) ?></div>
        <?php endif; ?>
    </div>
</section>