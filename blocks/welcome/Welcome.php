<?php
/**
 * Template for Welcome-blokken.
 *
 * @var string                            $title
 * @var string                            $intro
 * @var string                            $listTitle
 * @var string                            $footerText
 * @var array<int, array<string, string>> $items
 * @var string                            $cssVars
 * @var RenderContext                     $context
 *
 * nl2br() på brødteksten bevarer redaktørens linjeskift. Rækkefølgen er
 * vigtig: e() escaper FØRST, nl2br() tilføjer <br> BAGEFTER. Byttes de om,
 * ville de indsatte br-tags selv blive escapet og vist som tekst.
 *
 * I editoren bruges nl2br() IKKE. Der vises den rå tekst med linjeskift,
 * fordi det er præcis den værdi, der bliver gemt. CSS'en viser
 * linjeskiftene i stedet (white-space: pre-wrap).
 *
 * Tomme felter og tomme punkter tegnes også i editoren, så man kan klikke
 * i dem og udfylde dem.
 */
$editing = $context->isInlineEditing();
?>
<section class="block block--welcome"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="welcome__inner">
        <?php if ($title !== '' || $editing): ?>
            <h2 class="welcome__title"<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h2>
        <?php endif; ?>

        <?php if ($intro !== '' || $editing): ?>
            <p class="welcome__intro"<?= $context->inline('intro', 'Introtekst', true) ?>><?= $editing ? e($intro) : nl2br(e($intro)) ?></p>
        <?php endif; ?>

        <?php if ($items !== [] || $editing): ?>
            <div class="welcome__card">
                <?php if ($listTitle !== '' || $editing): ?>
                    <h3 class="welcome__list-title"<?= $context->inline('list_title', 'Overskrift over listen') ?>><?= e($listTitle) ?></h3>
                <?php endif; ?>

                <ul class="welcome__list">
                    <?php foreach ($items as $index => $item): ?>
                        <?php $text = trim((string) ($item['text'] ?? '')); ?>
                        <?php if ($text !== '' || $editing): ?>
                            <li<?= $context->inlineRow('items', $index, 'text', 'Punkt') ?>><?= e($text) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($footerText !== '' || $editing): ?>
            <p class="welcome__footer"<?= $context->inline('footer_text', 'Afsluttende tekst', true) ?>><?= $editing ? e($footerText) : nl2br(e($footerText)) ?></p>
        <?php endif; ?>
    </div>
</section>