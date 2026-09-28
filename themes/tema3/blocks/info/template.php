<?php
/**
 * Template for tema 3's info med billede.
 *
 * @var string                            $title
 * @var string                            $eyebrow
 * @var array<int, string>                $paragraphs  Færdig, sikker HTML.
 * @var string                            $note        Færdig, sikker HTML. Tom = ingen NB-linje.
 * @var string                            $bodyRaw     Rå tekst til editoren.
 * @var string                            $noteRaw     Rå tekst til editoren.
 * @var array<int, array<string, string>> $buttons     field, label, href.
 * @var string                            $image       Færdig, valideret URL.
 * @var string                            $imageAlt
 * @var string                            $cssVars
 * @var RenderContext                     $context
 *
 * I EDITOREN vises brødteksten og NB-linjen rå — med stjernerne synlige —
 * så de kan redigeres direkte uden at miste formateringen.
 */
$editing = $context->isInlineEditing();

?>
<section class="block block--tema3-info"<?= eAttr(['style' => $cssVars]) ?>>
    <?= Tema3Heading::render($title, $eyebrow, $context) ?>

    <div class="t3info__box">
        <div class="t3info__inner">
            <div class="t3info__text">
                <?php if ($editing): ?>
                    <p class="t3info__body t3info__body--raw"<?= $context->inline('body', 'Tekst — *fremhævet*, **ekstra fed**', true) ?>><?= e($bodyRaw) ?></p>
                    <p class="t3info__note"<?= $context->inline('note', 'NB-linje (tom = ingen)', true) ?>><?= e($noteRaw) ?></p>
                <?php else: ?>
                    <?php foreach ($paragraphs as $paragraph): ?>
                        <p class="t3info__body"><?= $paragraph ?></p>
                    <?php endforeach; ?>
                    <?php if ($note !== ''): ?>
                        <p class="t3info__note"><?= nl2br($note, false) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="t3info__media">
                <?php if ($image !== ''): ?>
                    <img src="<?= e($image) ?>" alt="<?= e($imageAlt) ?>" decoding="async"<?= $context->inlineImage('image') ?>>
                <?php else: ?>
                    <span class="t3info__media-empty"<?= $context->inlineImage('image') ?>><?= $editing ? 'Vælg billede' : '' ?></span>
                <?php endif; ?>

                <?php if ($buttons !== []): ?>
                    <div class="t3info__buttons">
                        <?php foreach ($buttons as $button): ?>
                            <a class="t3info__button" href="<?= e($button['href']) ?>">
                                <span<?= $context->inline($button['field'], 'Knaptekst') ?>><?= e($button['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php if (!$editing): ?>
<?php /*
    Indgangen: boksen glider roligt op, og tekst og billede toner frem.
    (Titellinjens egen animation står i Tema3Heading.php.)
    Ikke i editoren, og ikke hvis brugeren har slået animationer fra.
    Virker også i den eksporterede side.
*/ ?>
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    section.classList.add('t3info--armed');

    var image = section.querySelector('.t3info__media img');
    var ready = new Promise(function (resolve) {
        if (!image) {
            resolve();
            return;
        }
        var done = image.decode ? image.decode() : Promise.resolve();
        done.then(resolve, resolve);
        setTimeout(resolve, 2000);
    });

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                ready.then(function () {
                    entry.target.classList.add('is-visible');
                });
            }
        });
    }, { rootMargin: '0px 0px -25% 0px' }).observe(section);
}());
</script>
<?php endif; ?>
