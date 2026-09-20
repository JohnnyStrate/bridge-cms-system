<?php
/**
 * Template for Quote-blokken.
 *
 * @var string $image    Færdig, valideret URL. Tom = intet billede.
 * @var string $imageAlt
 * @var string $quote
 * @var string $body
 * @var string $note
 * @var string $cssVars
 *
 * Klassen på sektionen fortæller CSS'en, om der er et billede. Uden
 * billede fylder boksen hele bredden.
 */
?>
<section class="block block--quote<?= $image === '' ? ' quote--no-image' : '' ?>"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="quote__inner">
        <?php if ($image !== ''): ?>
            <figure class="quote__figure">
                <img class="quote__image" src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>">
            </figure>
        <?php endif; ?>

        <div class="quote__box">
            <?php if ($quote !== ''): ?>
                <?php /* nl2br bevarer de linjeskift, brugeren selv har sat i citatet. */ ?>
                <blockquote class="quote__text"><?= nl2br(e($quote)) ?></blockquote>
            <?php endif; ?>

            <?php if ($body !== ''): ?>
                <p class="quote__body"><?= nl2br(e($body)) ?></p>
            <?php endif; ?>

            <?php if ($note !== ''): ?>
                <p class="quote__note"><?= nl2br(e($note)) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
