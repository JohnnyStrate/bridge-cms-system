<?php
/**
 * Template for tema 1's footer.
 *
 * @var string                            $clubName
 * @var string                            $mark       Første bogstav i navnet.
 * @var string                            $tagline
 * @var string                            $address
 * @var string                            $email
 * @var string                            $emailHref  Tom, hvis ugyldig.
 * @var string                            $phone
 * @var string                            $phoneHref  Tom, hvis tom.
 * @var array<int, array<string, string>> $links
 * @var string                            $copyright
 * @var string                            $cssVars
 * @var RenderContext                     $context
 *
 * Adresserne er regnet ud i Tema1FooterBlock::render(). Navn og kontakt
 * kommer fra Indstillinger (SiteInfo) og redigeres ikke her.
 */
$hasContact = $address !== '' || $phone !== '' || $email !== '';
?>
<footer class="block block--tema1-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="t1foot__inner">

        <div class="t1foot__brand">
            <?php if ($mark !== ''): ?>
                <span class="t1foot__mark" aria-hidden="true"><?= e($mark) ?></span>
            <?php endif; ?>
            <div>
                <?php if ($clubName !== ''): ?>
                    <p class="t1foot__name"><?= e($clubName) ?></p>
                <?php endif; ?>
                <?php if ($tagline !== ''): ?>
                    <p class="t1foot__tagline"<?= $context->inline('tagline', 'Kort tekst') ?>><?= e($tagline) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasContact): ?>
            <div class="t1foot__col">
                <p class="t1foot__heading">Kontakt</p>
                <?php if ($address !== ''): ?>
                    <p class="t1foot__line"><?= nl2br(e($address)) ?></p>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <p class="t1foot__line">
                        <?php if ($phoneHref !== ''): ?>
                            <a href="<?= e($phoneHref) ?>"><?= e($phone) ?></a>
                        <?php else: ?>
                            <?= e($phone) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <p class="t1foot__line">
                        <?php if ($emailHref !== ''): ?>
                            <a href="<?= e($emailHref) ?>"><?= e($email) ?></a>
                        <?php else: ?>
                            <?= e($email) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($links !== []): ?>
            <nav class="t1foot__col" aria-label="Footermenu">
                <p class="t1foot__heading">Genveje</p>
                <ul class="t1foot__links">
                    <?php foreach ($links as $link): ?>
                        <li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>

    <?php if ($copyright !== ''): ?>
        <p class="t1foot__bottom"<?= $context->inline('copyright', 'Bundtekst') ?>><?= e($context->isInlineEditing() ? $copyrightRaw : $copyright) ?></p>
    <?php endif; ?>
</footer>
