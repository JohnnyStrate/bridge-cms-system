<?php
/**
 * Template for Standard Bridge 2's billedgitter.
 *
 * @var string                           $title
 * @var string                           $intro
 * @var string                           $headingStyle  'Boks' eller 'Stor tekst'.
 * @var array<int, array<string, mixed>> $tiles         index, label, href, image.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

// Et billede i en repeater-række kobles til rækkens billedfelt i panelet.
$imageAttr = static function (int $row) use ($editing): string {
    return $editing
        ? ' data-inline-repeater="tiles" data-inline-row="' . $row . '"'
            . ' data-inline-image="image" title="Klik for at skifte billede"'
        : '';
};
?>
<section class="block block--standard2-grid"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Standard2Kit::heading($title, $context, 'title', $headingStyle) ?>

    <?php if ($intro !== '' || $editing): ?>
        <p class="s2g__intro s2-reveal"<?= $context->inline('intro', 'Kort tekst (tom = ingen)', true) ?>><?= $editing ? e($intro) : nl2br(e($intro)) ?></p>
    <?php endif; ?>

    <?php if ($tiles !== []): ?>
        <div class="s2g__tiles">
            <?php foreach ($tiles as $n => $tile): ?>
                <?php $i = (int) $tile['index']; ?>
                <a class="s2g__tile s2-photolink s2-reveal" href="<?= e($tile['href']) ?>" style="--n:<?= (int) $n ?>">
                    <span class="s2-photolink__image" role="presentation"
                          <?= $tile['image'] !== '' ? 'style="background-image:url(\'' . e($tile['image']) . '\')"' : '' ?><?= $imageAttr($i) ?>></span>

                    <?php if ($tile['label'] !== '' || $editing): ?>
                        <span class="s2-photolink__label">
                            <span class="s2-underline"<?= $context->inlineRow('tiles', $i, 'label', 'Tekst på billedet') ?>><?= e($tile['label']) ?></span>
                            <svg class="s2-photolink__arrow" viewBox="0 0 10 10" aria-hidden="true" focusable="false">
                                <path d="M2 8 8 2M3.5 2H8v4.5" fill="none" stroke="currentColor"
                                      stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= Standard2Kit::reveal($context) ?>
