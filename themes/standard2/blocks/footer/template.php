<?php
/**
 * Template for Standard Bridge 2's footer.
 *
 * @var string                           $logo         Færdig, valideret URL.
 * @var string                           $logoAlt
 * @var array<int, array<string, mixed>> $links        index, label, href.
 * @var string                           $address      Kan have linjeskift.
 * @var string                           $phone
 * @var string                           $phoneHref    Tom, hvis tom.
 * @var string                           $email
 * @var string                           $emailHref    Tom, hvis ugyldig.
 * @var array<int, array<string, mixed>> $hours        index, day, text.
 * @var string                           $tagline
 * @var string                           $buttonLabel
 * @var string                           $buttonHref
 * @var string                           $copyright
 * @var string                           $copyrightRaw
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
$editing    = $context->isInlineEditing();
$hasContact = $address !== '' || $phone !== '' || $email !== '';

$arrow = '<svg class="s2ft__arrow" viewBox="0 0 10 10" aria-hidden="true" focusable="false">'
    . '<path d="M2 8 8 2M3.5 2H8v4.5" fill="none" stroke="currentColor" stroke-width="0.9"'
    . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<footer class="block block--standard2-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="s2ft__inner">
        <div class="s2ft__top s2-reveal">
            <a class="s2ft__logo" href="<?= e($links[0]['href'] ?? '#') ?>">
                <img src="<?= e($logo) ?>" alt="<?= e($logoAlt) ?>"<?= $editing ? ' title="Logoet skiftes under Indstillinger"' : '' ?>>
            </a>

            <?php if ($links !== []): ?>
                <nav aria-label="Footermenu">
                    <ul class="s2ft__links">
                        <?php foreach ($links as $link): ?>
                            <li><a class="s2-underline" href="<?= e($link['href']) ?>"<?= $context->inlineRow('links', (int) $link['index'], 'label', 'Tekst') ?>><?= e($link['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <div class="s2ft__cols">
            <?php if ($hasContact): ?>
                <div class="s2ft__col s2-reveal">
                    <p class="s2ft__heading">Kontakt</p>
                    <?php /* Kontakt kommer fra Indstillinger (SiteInfo). */ ?>
                    <?php if ($phone !== ''): ?>
                        <p class="s2ft__line">
                            <?php if ($phoneHref !== ''): ?>
                                <a class="s2-underline" href="<?= e($phoneHref) ?>"><?= e($phone) ?></a>
                            <?php else: ?>
                                <?= e($phone) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                        <p class="s2ft__line"><?= nl2br(e($address)) ?></p>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <p class="s2ft__line">
                            <?php if ($emailHref !== ''): ?>
                                <a class="s2-underline" href="<?= e($emailHref) ?>"><?= e($email) ?></a>
                            <?php else: ?>
                                <?= e($email) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($hours !== []): ?>
                <div class="s2ft__col s2-reveal">
                    <p class="s2ft__heading">Spilletider</p>
                    <dl class="s2ft__hours">
                        <?php foreach ($hours as $row): ?>
                            <div>
                                <dt<?= $context->inlineRow('hours', (int) $row['index'], 'day', 'Dag') ?>><?= e($row['day']) ?></dt>
                                <dd<?= $context->inlineRow('hours', (int) $row['index'], 'text', 'Tid og hvad') ?>><?= e($row['text']) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
            <?php endif; ?>

            <div class="s2ft__col s2ft__col--cta s2-reveal">
                <?php if ($tagline !== '' || $editing): ?>
                    <p class="s2ft__tagline"<?= $context->inline('tagline', 'Kort tekst', true) ?>><?= $editing ? e($tagline) : nl2br(e($tagline)) ?></p>
                <?php endif; ?>

                <?php if ($buttonLabel !== '' || $editing): ?>
                    <a class="s2ft__button" href="<?= e($buttonHref) ?>">
                        <span class="s2-underline"<?= $context->inline('button_label', 'Knaptekst') ?>><?= e($buttonLabel) ?></span>
                        <?= $arrow ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="s2ft__bottom">
            <p class="s2ft__copy"<?= $context->inline('copyright', 'Bundtekst') ?>><?= e($editing ? $copyrightRaw : $copyright) ?></p>

            <a class="s2ft__top-link" href="#">
                <span class="s2-underline">Til toppen</span>
                <svg viewBox="0 0 12 16" aria-hidden="true" focusable="false"><path d="M6 14V2.5M2 6.5 6 2.5l4 4" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </div>
</footer>
<?= Standard2Kit::reveal($context) ?>
