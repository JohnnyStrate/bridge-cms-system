<?php
/**
 * Template for tema 2's footer.
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
 * @var array<int, array<string, string>> $hours       day, text
 * @var array<int, array<string, string>> $links       label, href
 * @var string                            $copyright
 * @var string                            $cssVars
 * @var RenderContext                     $context
 */
$editing  = $context->isInlineEditing();
$hasContact = $address !== '' || $phone !== '' || $email !== '';
?>
<footer class="block block--tema2-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <?php /* Hero'ens bølge, vendt på hovedet og fyldt med farven ovenover. */ ?>
    <svg class="t2f__wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 26 C 70 4 170 2 330 12 C 640 32 990 70 1235 86 C 1345 93 1415 84 1440 50 V100 H0 Z"/>
    </svg>

    <?php /* Stor, svag spar i baggrunden. Ren pynt. */ ?>
    <svg class="t2f__watermark" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M12 2.5s-8 5.8-8 11.1a4.2 4.2 0 0 0 7 3.1L9.8 21.5h4.4L13 16.7a4.2 4.2 0 0 0 7-3.1c0-5.3-8-11.1-8-11.1z"/>
    </svg>

    <div class="t2f__inner">
        <div class="t2f__brand t2f__reveal" style="--i:0">
            <?php if ($logo !== ''): ?>
                <img class="t2f__logo" src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $context->inlineImage('logo') ?>>
            <?php elseif ($editing): ?>
                <span class="t2f__logo t2f__logo--empty"<?= $context->inlineImage('logo') ?>>Logo</span>
            <?php endif; ?>

            <?php if ($tagline !== '' || $editing): ?>
                <p class="t2f__tagline"<?= $context->inline('tagline', 'Kort tekst om klubben', true) ?>><?= $editing ? e($tagline) : nl2br(e($tagline)) ?></p>
            <?php endif; ?>

            <?php if ($buttonLabel !== '' || $editing): ?>
                <a class="t2f__button" href="<?= e($buttonHref) ?>">
                    <span class="t2f__button-label"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                    <svg class="t2f__arrow" viewBox="0 0 40 12" aria-hidden="true" focusable="false">
                        <path d="M0 6h38M33 1l5 5-5 5" fill="none" stroke="currentColor"
                              stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($hasContact || $editing): ?>
            <div class="t2f__col t2f__reveal" style="--i:1">
                <p class="t2f__heading">Kontakt</p>
                <?php if ($address !== '' || $editing): ?>
                    <p class="t2f__line"<?= $context->inline('address', 'Adresse', true) ?>><?= $editing ? e($address) : nl2br(e($address)) ?></p>
                <?php endif; ?>
                <?php if ($phone !== '' || $editing): ?>
                    <p class="t2f__line">
                        <?php if ($phoneHref !== '' && !$editing): ?>
                            <a href="<?= e($phoneHref) ?>"><?= e($phone) ?></a>
                        <?php else: ?>
                            <span<?= $context->inline('phone', 'Telefon') ?>><?= e($phone) ?></span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if ($email !== '' || $editing): ?>
                    <p class="t2f__line">
                        <?php if ($emailHref !== '' && !$editing): ?>
                            <a href="<?= e($emailHref) ?>"><?= e($email) ?></a>
                        <?php else: ?>
                            <span<?= $context->inline('email', 'E-mail') ?>><?= e($email) ?></span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($hours !== []): ?>
            <div class="t2f__col t2f__reveal" style="--i:2">
                <p class="t2f__heading">Spilletider</p>
                <dl class="t2f__hours">
                    <?php foreach ($hours as $index => $row): ?>
                        <div>
                            <dt<?= $context->inlineRow('hours', $index, 'day', 'Dag') ?>><?= e($row['day']) ?></dt>
                            <dd<?= $context->inlineRow('hours', $index, 'text', 'Tid og hvad') ?>><?= e($row['text']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        <?php endif; ?>

        <?php if ($links !== []): ?>
            <nav class="t2f__col t2f__reveal" style="--i:3" aria-label="Footermenu">
                <p class="t2f__heading">Genveje</p>
                <ul class="t2f__links">
                    <?php foreach ($links as $link): ?>
                        <li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <div class="t2f__bottom">
        <p class="t2f__copy"<?= $context->inline('copyright', 'Bundtekst') ?>><?= e($copyright) ?></p>

        <span class="t2f__suits" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="t2f__suit"><path d="M12 2.5s-8 5.8-8 11.1a4.2 4.2 0 0 0 7 3.1L9.8 21.5h4.4L13 16.7a4.2 4.2 0 0 0 7-3.1c0-5.3-8-11.1-8-11.1z"/></svg>
            <svg viewBox="0 0 24 24" class="t2f__suit t2f__suit--red"><path d="M12 21s-8.5-5.3-8.5-11.1A4.7 4.7 0 0 1 12 7.3a4.7 4.7 0 0 1 8.5 2.6C20.5 15.7 12 21 12 21z"/></svg>
            <svg viewBox="0 0 24 24" class="t2f__suit t2f__suit--red"><path d="M12 2 20 12 12 22 4 12z"/></svg>
            <svg viewBox="0 0 24 24" class="t2f__suit"><circle cx="12" cy="7" r="4.7"/><circle cx="6.6" cy="13.8" r="4.7"/><circle cx="17.4" cy="13.8" r="4.7"/><path d="M12 11.5 10 22.5h4z"/></svg>
        </span>

        <a class="t2f__top" href="#">
            Til toppen
            <svg viewBox="0 0 12 16" aria-hidden="true" focusable="false"><path d="M6 15V2M1.5 6.5 6 2l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</footer>
<?php if (!$editing): ?>
<?php /* Kolonnerne toner ind én ad gangen, når footeren kommer frem. */ ?>
<script>
(function () {
    var footer = document.currentScript && document.currentScript.previousElementSibling;
    if (!footer || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    footer.classList.add('t2f--armed');
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
