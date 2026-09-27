<?php
/**
 * Template for tema 3's footer.
 *
 * @var string                            $logo        Færdig, valideret URL.
 * @var string                            $logoAlt
 * @var string                            $tagline
 * @var string                            $buttonLabel
 * @var string                            $buttonHref
 * @var string                            $address     Kan have linjeskift.
 * @var string                            $phone
 * @var string                            $phoneHref   Tom, hvis tom.
 * @var string                            $email
 * @var string                            $emailHref   Tom, hvis ugyldig.
 * @var array<int, array<string, mixed>>  $hours       index, day, text
 * @var array<int, array<string, mixed>>  $links       index, label, href
 * @var string                            $copyright
 * @var string                            $copyrightRaw
 * @var string                            $cssVars
 * @var RenderContext                     $context
 */
$editing    = $context->isInlineEditing();
$hasContact = $address !== '' || $phone !== '' || $email !== '';
?>
<footer class="block block--tema3-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t3f__intro t3f__reveal" style="--i:0">
        <img class="t3f__logo" src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $editing ? ' title="Logoet skiftes under Indstillinger"' : '' ?>>

        <?php if ($tagline !== '' || $editing): ?>
            <p class="t3f__tagline"<?= $context->inline('tagline', 'Kort tekst om klubben', true) ?>><?= $editing ? e($tagline) : nl2br(e($tagline)) ?></p>
        <?php endif; ?>

        <?php if ($buttonLabel !== '' || $editing): ?>
            <a class="t3f__button" href="<?= e($buttonHref) ?>">
                <span<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
            </a>
        <?php endif; ?>
    </div>

    <div class="t3f__cols">
        <?php if ($hasContact): ?>
            <div class="t3f__col t3f__reveal" style="--i:1">
                <p class="t3f__heading">Kontakt</p>
                <?php /* Kontakt kommer fra Indstillinger (SiteInfo). */ ?>
                <?php if ($address !== ''): ?>
                    <p class="t3f__line"><?= nl2br(e($address)) ?></p>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <p class="t3f__line">
                        <?php if ($phoneHref !== ''): ?>
                            <a href="<?= e($phoneHref) ?>"><?= e($phone) ?></a>
                        <?php else: ?>
                            <?= e($phone) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <p class="t3f__line">
                        <?php if ($emailHref !== ''): ?>
                            <a href="<?= e($emailHref) ?>"><?= e($email) ?></a>
                        <?php else: ?>
                            <?= e($email) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($hours !== []): ?>
            <div class="t3f__col t3f__reveal" style="--i:2">
                <p class="t3f__heading">Spilletider</p>
                <dl class="t3f__hours">
                    <?php foreach ($hours as $row): ?>
                        <div>
                            <dt<?= $context->inlineRow('hours', (int) $row['index'], 'day', 'Dag') ?>><?= e($row['day']) ?></dt>
                            <dd<?= $context->inlineRow('hours', (int) $row['index'], 'text', 'Tid og hvad') ?>><?= e($row['text']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        <?php endif; ?>

        <?php if ($links !== []): ?>
            <nav class="t3f__col t3f__reveal" style="--i:3" aria-label="Footermenu">
                <p class="t3f__heading">Genveje</p>
                <ul class="t3f__links">
                    <?php foreach ($links as $link): ?>
                        <li><a href="<?= e($link['href']) ?>"><span<?= $context->inlineRow('links', (int) $link['index'], 'label', 'Tekst') ?>><?= e($link['label']) ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <div class="t3f__bar">
        <p class="t3f__copy"<?= $context->inline('copyright', 'Bundtekst') ?>><?= e($editing ? $copyrightRaw : $copyright) ?></p>

        <a class="t3f__top" href="#" aria-label="Til toppen af siden">
            <span class="t3f__top-text">Til toppen</span>
            <span class="t3f__top-bubble" aria-hidden="true">
                <svg viewBox="0 0 12 16" focusable="false"><path d="M6 14V2.5M1.8 6.5 6 2.3l4.2 4.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    </div>
</footer>
<?php if (!$editing): ?>
<?php /* Indholdet stiger roligt op, når footeren kommer frem. */ ?>
<script>
(function () {
    var footer = document.currentScript && document.currentScript.previousElementSibling;
    if (!footer || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    footer.classList.add('t3f--armed');
    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px' }).observe(footer);
}());
</script>
<?php endif; ?>
