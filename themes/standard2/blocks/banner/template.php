<?php
/**
 * Template for Standard Bridge 2's billedbanner.
 *
 * @var string        $label
 * @var string        $href
 * @var string        $image    Færdig, valideret URL.
 * @var string        $cssVars
 * @var RenderContext $context
 *
 * Billedet står i style-attributten, fordi det er forskelligt fra blok til
 * blok. Det er sikkert, fordi FieldValidator allerede har afvist alt andet
 * end en simpel filsti.
 */
?>
<section class="block block--standard2-banner"<?= eAttr(['style' => $cssVars]) ?>>
    <a class="s2b__link s2-reveal" href="<?= e($href) ?>">
        <span class="s2b__image" role="presentation"
              <?= $image !== '' ? 'style="background-image:url(\'' . e($image) . '\')"' : '' ?><?= $context->inlineImage('image') ?>></span>

        <span class="s2b__label">
            <span class="s2-underline"<?= $context->inline('label', 'Tekst') ?>><?= e($label) ?></span>
            <svg class="s2b__arrow" viewBox="0 0 10 10" aria-hidden="true" focusable="false">
                <path d="M2 8 8 2M3.5 2H8v4.5" fill="none" stroke="currentColor"
                      stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </a>
</section>
<?= Standard2Kit::reveal($context) ?>
