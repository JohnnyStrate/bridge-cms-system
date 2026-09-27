<?php
/**
 * Template for Bridge Card-kort med ikoner.
 *
 * @var string                           $eyebrow
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, mixed>> $cards  index, icon, title, text, buttonLabel, href
 * @var bool                             $alignRight
 * @var string                           $decorLeft   Færdig URL eller ''.
 * @var string                           $decorRight
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--bridgecardtheme-cards<?= $alignRight ? ' bccards--right' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>>
    <?php if ($decorLeft !== '' || $editing): ?>
        <div class="bccards__decor bccards__decor--left"<?= $context->inlineImage('decor_left') ?>>
            <?php if ($decorLeft !== ''): ?><img src="<?= e($decorLeft) ?>" alt=""><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($decorRight !== '' || $editing): ?>
        <div class="bccards__decor bccards__decor--right"<?= $context->inlineImage('decor_right') ?>>
            <?php if ($decorRight !== ''): ?><img src="<?= e($decorRight) ?>" alt=""><?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="bccards__inner">
        <header class="bccards__head">
            <?php if ($eyebrow !== '' || $editing): ?>
                <p class="bccards__eyebrow">
                    <span<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></span>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= BridgeCardKit::CLUB ?></svg>
                </p>
            <?php endif; ?>

            <h2 class="bccards__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>

            <?php if ($text !== '' || $editing): ?>
                <p class="bccards__text"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
            <?php endif; ?>
        </header>

        <?php if ($cards !== []): ?>
            <div class="bccards__grid" style="--count:<?= count($cards) ?>">
                <?php foreach ($cards as $card): ?>
                    <article class="bccards__card">
                        <?php if ($card['icon'] !== ''): ?>
                            <svg class="bccards__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= $card['icon'] ?></svg>
                        <?php endif; ?>

                        <h3 class="bccards__card-title"<?= $context->inlineRow('cards', (int) $card['index'], 'title', 'Titel') ?>><?= e($card['title']) ?></h3>
                        <p class="bccards__card-text"<?= $context->inlineRow('cards', (int) $card['index'], 'text', 'Tekst') ?>><?= e($card['text']) ?></p>

                        <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                            <a class="bccards__button" href="<?= e($card['href']) ?>"<?= $context->inlineRow('cards', (int) $card['index'], 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
