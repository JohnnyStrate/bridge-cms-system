<?php
/**
 * Template for tema 3's tider og steder.
 *
 * @var string                           $title
 * @var string                           $eyebrow
 * @var array<int, array<string, mixed>> $items    index, title, text, linkLabel, href.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

?>
<section class="block block--tema3-schedule"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Tema3Heading::render($title, $eyebrow, $context) ?>

    <?php if ($items !== []): ?>
        <ul class="t3sch__list">
            <?php foreach ($items as $n => $item): ?>
                <?php $i = (int) $item['index']; ?>
                <li class="t3sch__item" style="--i:<?= (int) $n ?>">
                    <h3 class="t3sch__item-title"<?= $context->inlineRow('items', $i, 'title', 'Overskrift') ?>><?= e($item['title']) ?></h3>

                    <p class="t3sch__box"<?= $context->inlineRow('items', $i, 'text', 'Tekst i boksen') ?>><?= e($item['text']) ?></p>

                    <?php if ($item['linkLabel'] !== '' || $editing): ?>
                        <a class="t3sch__link" href="<?= e($item['href']) ?>">
                            <span class="t3sch__link-label"<?= $context->inlineRow('items', $i, 'link_label', 'Linktekst') ?>><?= e($item['linkLabel']) ?></span>
                            <span class="t3sch__line" aria-hidden="true"></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: punkterne toner roligt frem ét ad gangen — overskriften
    glider ind fra venstre, boksen fra højre. (Titellinjens egen animation
    står i Tema3Heading.php.) Ikke i editoren, og ikke hvis brugeren har
    slået animationer fra.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    section.classList.add('t3sch--armed');

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                entry.target.classList.add('is-visible');
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
