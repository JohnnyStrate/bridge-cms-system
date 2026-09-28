<?php
/**
 * Template for Bridge Card-kort med ikoner.
 *
 * @var string                           $eyebrow
 * @var string                           $title
 * @var string                           $text
 * @var array<int, array<string, mixed>> $cards  index, icon, title, text, buttonLabel, href
 * @var bool                             $alignRight
 * @var string                           $suit        Kuløren ved overlinjen.
 * @var string                           $decorBig    Pyntens store former.
 * @var string                           $decorSmall  Pyntens små former.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();
$side    = $alignRight ? 'right' : 'left';

// Pynten: [stor/lille, animationsretning]. Placeringen står i block.css.
$decor = [
    ['big', 'top'], ['big', 'top'], ['small', 'center'],
    ['big', 'center'], ['big', 'right'], ['small', 'top'],
];
?>
<?= BridgeCardKit::stylesheet($context) ?>
<section class="block block--bridgecardtheme-cards<?= $alignRight ? ' bccards--right' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bccards__decor" aria-hidden="true">
        <?php foreach ($decor as $n => [$size, $from]): ?>
            <span class="bccards__shape bccards__shape--<?= $n + 1 ?> bc-mask" data-bc-reveal="<?= $from ?>" style="--i:<?= $n ?>"<?= BridgeCardKit::live($context, $size === 'big' ? 'decor_suit' : 'decor_suit_small', $size === 'big' ? $decorBig : $decorSmall) ?>></span>
        <?php endforeach; ?>
    </div>

    <div class="bccards__inner">
        <header class="bccards__head">
            <?php if ($eyebrow !== '' || $editing): ?>
                <p class="bccards__eyebrow" data-bc-reveal="top">
                    <span<?= $context->inline('eyebrow', 'Overlinje') ?>><?= e($eyebrow) ?></span>
                    <span class="bc-icon bc-mask" aria-hidden="true"<?= BridgeCardKit::live($context, 'suit', $suit) ?>></span>
                </p>
            <?php endif; ?>

            <h2 class="bccards__title" data-bc-reveal="<?= $side ?>" style="--i:1"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>

            <?php if ($text !== '' || $editing): ?>
                <p class="bccards__text" data-bc-reveal="<?= $side ?>" style="--i:2"<?= $context->inline('text', 'Tekst', true) ?>><?= e($text) ?></p>
            <?php endif; ?>
        </header>

        <?php if ($cards !== []): ?>
            <div class="bccards__grid">
                <?php foreach ($cards as $n => $card): ?>
                    <article class="bccards__card" data-bc-reveal="bottom" style="--i:<?= 3 + (int) $n ?>">
                        <span class="bccards__icon bc-icon bc-mask" aria-hidden="true"<?= BridgeCardKit::live($context, 'icon', $card['icon'], 'cards', (int) $card['index']) ?>></span>

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
<?= BridgeCardKit::revealScript($context) ?>
