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
    <!-- //DEN DER NEDTIMER -->
    <div class="div">


    </div>
</section>
<script>
const linkBtns = document.querySelectorAll('.textbox__button');
const div = document.querySelector('.div');

linkBtns.forEach((linkBtn) => {
    linkBtn.addEventListener('click', (e) => {
        console.log('hej');
        div.innerHTML='<p>Tryk på </p> <button type="button div" class="ed-btn ed-btn--edit">&#9998;</button><p>for at indsætte link</p>';
        div.classList.toggle('show');

        setTimeout(() => {
            div.classList.remove('show');
        }, 5000);

    });
});
</script>
<style>
    .div{
        width:300px;
        height:300px;
        background-color:#4A6FA5;
        display:flex;
        flex-direction: column;
        text-align:center;
        justify-content:center;
        padding:10px;
        align-items:center;
        font-size:25px;
        position: absolute;
        z-index:1;
        left:45%;
        bottom:50%;
        color:white;
        display:none;
        border-radius:10px;
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;

    
    }
    .div.show{
       display:flex;
       color:white;
    }
    .button.div{}
</style>