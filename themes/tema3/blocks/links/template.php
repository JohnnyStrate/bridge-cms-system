<?php
/**
 * Template for tema 3's linkkort.
 *
 * @var string                           $title
 * @var string                           $eyebrow
 * @var array<int, array<string, mixed>> $cards    index, title, text, buttonLabel, href.
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing = $context->isInlineEditing();

?>
<section class="block block--tema3-links"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Tema3Heading::render($title, $eyebrow, $context) ?>

    <?php if ($cards !== []): ?>
        <ul class="t3lk__cards">
            <?php foreach ($cards as $n => $card): ?>
                <?php $i = (int) $card['index']; ?>
                <li class="t3lk__card" style="--i:<?= (int) $n ?>">
                    <h3 class="t3lk__card-title"<?= $context->inlineRow('cards', $i, 'title', 'Overskrift') ?>><?= e($card['title']) ?></h3>
                    <p class="t3lk__card-text"<?= $context->inlineRow('cards', $i, 'text', 'Tekst') ?>><?= e($card['text']) ?></p>

                    <?php if ($card['buttonLabel'] !== '' || $editing): ?>
                        <a class="t3lk__button" href="<?= e($card['href']) ?>">
                            <span<?= $context->inlineRow('cards', $i, 'button_label', 'Knaptekst') ?>><?= e($card['buttonLabel']) ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: kortene stiger roligt op ét ad gangen. (Titellinjens egen
    animation står i Tema3Heading.php.) Ikke i editoren, og ikke hvis
    brugeren har slået animationer fra.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    section.classList.add('t3lk--armed');

    var fonts = document.fonts && document.fonts.ready
        ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 2000); })])
        : Promise.resolve();

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
