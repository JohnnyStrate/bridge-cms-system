<?= 
var RenderContext
$editing->$context->isInlineEditing()
?>
<nav class="block sbnav"<?= eAttr(['style' => $cssVars]) ?>>
    <p class="sbnav__title"<?= $context->inline('title') ?>><?= e($title) ?></p>

    <ul class="sbnav__links">
        <?php foreach ($links as $i => $link): ?>
            <li>
                <a href="<?= e($link['page'] > 0 ? $context->pageUrl($link['page']) : $link['url']) ?>"<?= $context->inlineRow('links', $i, 'label') ?>><?= e($link['label']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($ctaLabel !== ''): ?>
        <a class="sbnav__cta" href="<?= e($ctaHref) ?>"<?= $context->inline('cta_label') ?>><?= e($ctaLabel) ?></a>
    <?php endif; ?>
</nav>