<?php
/**
 * Template for Standard Bridge 2's velkommen + seneste nyt.
 *
 * @var string                           $title
 * @var string                           $intro
 * @var string                           $newsLabel  Tom = ingen opslag.
 * @var array<int, array<string, mixed>> $news       index, title, text.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing  = $context->isInlineEditing();
$showNews = $news !== [] && ($newsLabel !== '' || $editing);
?>
<section class="block block--standard2-welcome"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Standard2Kit::heading($title, $context) ?>

    <?php if ($intro !== '' || $editing): ?>
        <p class="s2w__intro s2-reveal"<?= $context->inline('intro', 'Kort tekst', true) ?>><?= $editing ? e($intro) : nl2br(e($intro)) ?></p>
    <?php endif; ?>

    <?php if ($showNews || $editing): ?>
        <div class="s2w__news">
            <p class="s2w__label s2-reveal"<?= $context->inline('news_label', 'Lille overskrift') ?>><?= e($newsLabel) ?></p>

            <?php if ($news !== []): ?>
                <div class="s2w__box s2-reveal" style="--count:<?= count($news) ?>">
                    <?php foreach ($news as $n => $item): ?>
                        <?php if ($n > 0): ?>
                            <span class="s2w__divider" aria-hidden="true"></span>
                        <?php endif; ?>
                        <article class="s2w__item">
                            <h3 class="s2w__item-title"<?= $context->inlineRow('news', (int) $item['index'], 'title', 'Overskrift') ?>><?= e($item['title']) ?></h3>
                            <p class="s2w__item-text"<?= $context->inlineRow('news', (int) $item['index'], 'text', 'Tekst') ?>><?= e($item['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?= Standard2Kit::reveal($context) ?>
