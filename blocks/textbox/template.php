<?php
/**
 * Template for Tekstboks-blokken.
 *
 * @var string $title
 * @var string $body
 * @var string $cardText
 * @var string $cardLabel
 * @var string $cardHref
 * @var string $footerTitle
 * @var string $footerLabel
 * @var string $footerHref
 * @var string $cssVars
 * @var RenderContext
 *
 * Kortet står FØR teksten i markup'en. Det er et krav, når et element
 * flyder: browseren lader kun tekst, der kommer efter, lægge sig omkring
 * det. Stod kortet efter teksten, ville det havne under den.
 */
$editing = $context->isInlineEditing();

?>
<section class="block block--textbox"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="textbox__inner">

        <?php if ($cardText !== '' || $cardLabel !== ''): ?>
            <aside class="textbox__card">
                <?php if ($cardText !== ''): ?>
                    <p class="textbox__card-text" <?= $context->inline('cardText', 'Tekst')?> ><?= nl2br(e($cardText)) ?></p>
                <?php endif; ?>

                <?php if ($cardLabel !== ''): ?>
                    <a class="textbox__button" href="<?= e($cardHref) ?>"><?= e($cardLabel) ?></a>
                <?php endif; ?>
            </aside>
        <?php endif; ?>

        <?php if ($title !== ''): ?>
            <h2 class="textbox__title" <?= $context->inline('title','Overskrift') ?>><?= e($title) ?></h2>
        <?php endif; ?>

        <?php if ($body !== ''): ?>
            <?php /*
                nl2br bevarer redaktørens linjeskift. Rækkefølgen er
                vigtig: e() escaper FØRST, nl2br() tilføjer <br> BAGEFTER.
                Byttes de om, ville br-tags selv blive escapet og vist
                som tekst.
            */ ?>
            <div class="textbox__body" <?= $context->inline('body','Tekst') ?>><?= nl2br(e($body)) ?></div>
        <?php endif; ?>

        <?php if ($footerTitle !== '' || $footerLabel !== ''): ?>
            <?php /*
                clear afslutter flydningen, så afslutningen altid ligger
                UNDER kortet — også når teksten er kortere end kortet.
            */ ?>
            <div class="textbox__footer" >
                <?php if ($footerTitle !== ''): ?>
                    <h3 class="textbox__footer-title" <?= $context->inline('footerTitle', 'Overskrift (tom = ingen afslutning)') ?>><?= e($footerTitle) ?></h3>
                <?php endif; ?>

                <?php if ($footerLabel !== ''): ?>
                    <a class="textbox__button" <?= $context->inline('cardHref', 'Ekstern adresse') ?>href="<?= e($footerHref) ?>"><?= e($footerLabel) ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
