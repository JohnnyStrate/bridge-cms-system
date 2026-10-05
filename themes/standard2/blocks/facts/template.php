<?php
/**
 * Template for Standard Bridge 2's fakta-boks.
 *
 * @var string                           $title
 * @var string                           $headingStyle  'Boks' eller 'Stor tekst'.
 * @var array<int, array<string, mixed>> $facts         index, label, value, valueRaw.
 * @var string                           $signature     Med {klub} udfyldt. Tom = ingen.
 * @var string                           $signatureRaw  Til editoren.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 *
 * I editoren vises {klub} som det står, så det kan redigeres. På siden
 * står klubbens navn.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--standard2-facts"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Standard2Kit::heading($title, $context, 'title', $headingStyle) ?>

    <div class="s2f__box s2-reveal">
        <?php if ($facts !== []): ?>
            <dl class="s2f__facts">
                <?php foreach ($facts as $fact): ?>
                    <?php $i = (int) $fact['index']; ?>
                    <div class="s2f__fact">
                        <dt class="s2f__label"<?= $context->inlineRow('facts', $i, 'label', 'Lille overskrift') ?>><?= e($fact['label']) ?></dt>
                        <dd class="s2f__value"<?= $context->inlineRow('facts', $i, 'value', 'Indhold') ?>><?= e($editing ? $fact['valueRaw'] : $fact['value']) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <?php if ($signature !== '' || $editing): ?>
            <p class="s2f__signature">
                <span<?= $context->inline('signature', 'Underskrift (tom = ingen)') ?>><?= e($editing ? $signatureRaw : $signature) ?></span>
            </p>
        <?php endif; ?>
    </div>
</section>
<?= Standard2Kit::reveal($context) ?>
