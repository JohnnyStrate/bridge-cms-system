<?php
/**
 * Template for Standard Bridge 2's kort med billeder.
 *
 * @var string                           $title
 * @var string                           $intro
 * @var string                           $headingStyle  'Boks' eller 'Stor tekst'.
 * @var array<int, array<string, mixed>> $rows          index, title, text, label, href, image.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

// Et billede i en repeater-række kobles til rækkens billedfelt i panelet.
$imageAttr = static function (int $row) use ($editing): string {
    return $editing
        ? ' data-inline-repeater="rows" data-inline-row="' . $row . '"'
            . ' data-inline-image="image" title="Klik for at skifte billede"'
        : '';
};
?>
<section class="block block--standard2-cards"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Standard2Kit::heading($title, $context, 'title', $headingStyle) ?>

    <?php if ($intro !== '' || $editing): ?>
        <p class="s2c__intro s2-reveal"<?= $context->inline('intro', 'Kort tekst (tom = ingen)', true) ?>><?= $editing ? e($intro) : nl2br(e($intro)) ?></p>
    <?php endif; ?>

    <?php if ($rows !== []): ?>
        <div class="s2c__rows">
            <?php foreach ($rows as $row): ?>
                <?php $i = (int) $row['index']; ?>
                <div class="s2c__row s2-reveal">
                    <div class="s2c__card<?= $row['title'] === '' && !$editing ? ' s2c__card--text-only' : '' ?>">
                        <?php if ($row['title'] !== '' || $editing): ?>
                            <h3 class="s2c__card-title"<?= $context->inlineRow('rows', $i, 'title', 'Overskrift (tom = kun tekst)') ?>><?= e($row['title']) ?></h3>
                        <?php endif; ?>
                        <p class="s2c__card-text"<?= $context->inlineRow('rows', $i, 'text', 'Tekst') ?>><?= e($row['text']) ?></p>
                    </div>

                    <a class="s2c__photo s2-photolink" href="<?= e($row['href']) ?>">
                        <span class="s2-photolink__image" role="presentation"
                              <?= $row['image'] !== '' ? 'style="background-image:url(\'' . e($row['image']) . '\')"' : '' ?><?= $imageAttr($i) ?>></span>

                        <?php if ($row['label'] !== '' || $editing): ?>
                            <span class="s2-photolink__label">
                                <span class="s2-underline"<?= $context->inlineRow('rows', $i, 'link_label', 'Tekst på billedet') ?>><?= e($row['label']) ?></span>
                                <svg class="s2-photolink__arrow" viewBox="0 0 10 10" aria-hidden="true" focusable="false">
                                    <path d="M2 8 8 2M3.5 2H8v4.5" fill="none" stroke="currentColor"
                                          stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= Standard2Kit::reveal($context) ?>
