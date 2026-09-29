<?php
/**
 * Template for Standard Bridge 2's hero.
 *
 * @var string                            $title     Med {klub} udfyldt.
 * @var string                            $titleRaw  Til editoren.
 * @var array<int, string>                $contact   Telefon og adresse.
 * @var array<int, array<string, mixed>>  $buttons   field, label, href, primary.
 * @var string                            $image     Færdig, valideret URL.
 * @var string                            $cssVars
 * @var RenderContext                     $context
 *
 * Baggrundsbilledet står i style-attributten, fordi det er forskelligt fra
 * blok til blok. Det er sikkert, fordi FieldValidator allerede har afvist
 * alt andet end en simpel filsti.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--standard2-hero"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="s2hero__bg" role="presentation"
         <?= $image !== '' ? 'style="background-image:url(\'' . e($image) . '\')"' : '' ?><?= $context->inlineImage('bg_image') ?>></div>

    <div class="s2hero__content">
        <div class="s2hero__heading s2hero__wipe" style="--i:0">
            <h1 class="s2hero__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($editing ? $titleRaw : $title) ?></h1>
            <span class="s2hero__line" aria-hidden="true"></span>
        </div>

        <?php if ($contact !== []): ?>
            <p class="s2hero__contact s2hero__wipe" style="--i:1"<?= $editing ? ' title="Telefon og adresse skiftes under Indstillinger"' : '' ?>>
                <?php foreach ($contact as $line): ?>
                    <span><?= e($line) ?></span>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>

        <?php if ($buttons !== []): ?>
            <div class="s2hero__buttons s2hero__wipe" style="--i:2">
                <?php foreach ($buttons as $button): ?>
                    <a class="s2hero__button<?= $button['primary'] ? ' s2hero__button--filled' : '' ?>" href="<?= e($button['href']) ?>">
                        <span class="s2hero__button-label s2-underline"<?= $context->inline($button['field'], 'Knaptekst') ?>><?= e($button['label']) ?></span>
                        <?php if ($button['primary']): ?>
                            <svg class="s2hero__arrow" viewBox="0 0 10 10" aria-hidden="true" focusable="false">
                                <path d="M2 8 8 2M3.5 2H8v4.5" fill="none" stroke="currentColor"
                                      stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: overskrift, streg, kontaktlinjer og knapper wipes ind fra
    venstre i en lige linje — én ad gangen. Scriptet står lige efter
    sektionen, så den skjules, før siden tegnes første gang. Ikke i
    editoren, og ikke hvis brugeren har slået animationer fra.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    section.classList.add('s2hero--armed');
    // To billeder (frames) senere: så har browseren tegnet den skjulte
    // udgave, og overgangen kører.
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            section.classList.add('is-visible');
        });
    });
}());
</script>
<?php endif; ?>
