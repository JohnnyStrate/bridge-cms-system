<?php
/**
 * Template for Box cards-blokken.
 *
 * @var string                            $title
 * @var array<int, array<string, string>> $cards  Hver med text, label og href.
 * @var string                            $align  'center' eller 'left'.
 * @var string                            $cssVars
 * @var RenderContext 
 * Adresserne er allerede regnet ud i BoxCardsBlock::render().
 */
  $context->isInlineEditing();
                     
?>
<section class="block block--boxcards"<?= eAttr(['style' => $cssVars]) ?>>
    <?php if ($title !== ''): ?>
        <h2 class="boxcards__title" <?=  $context->inline('title','text') ?>><?= e($title) ?></h2>
    <?php endif; ?>

    <?php if ($cards !== []): ?>
        <div class="boxcards__panel">
            <div class="boxcards__grid boxcards__grid--<?= e($align) ?>">
                <?php foreach ($cards as $card): ?>
                    <article class="boxcards__card"  <?=  $context->inline('background_color','Baggrund bag kortene') ?> >
                        <?php /*
                            nl2br bevarer linjeskift, som brugeren har
                            skrevet dem. Teksten er escapet først, så
                            HTML i feltet vises som tekst.
                        */ ?>
                        <p class="boxcards__text" <?=  $context->inline('text','Tekst') ?>><?= nl2br(e($card['text'])) ?></p>

                        <?php if ($card['label'] !== ''): ?>
                            <a <?=  $context->inline('text','Knaptekst (tom = ingen knap)') ?> class="boxcards__button" href="<?= e($card['href']) ?>">
                                <?= e($card['label']) ?>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
