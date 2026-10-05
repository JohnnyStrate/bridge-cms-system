<?php
/**
 * Template for standardbridge-footer.
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
 * Adresserne er regnet ud i StandardBridgeFooter::render(). Navn og kontakt
 * kommer fra Indstillinger (SiteInfo) og redigeres ikke her.
 */
$hasContact = $address !== '' || $phone !== '' || $email !== '';
?>
<footer class="block block--standardbridge-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="sbfoot__inner">

        <div class="sbfoot__brand">
            <?php if ($mark !== ''): ?>
                <span class="sbfoot__mark" aria-hidden="true"><?= e($mark) ?></span>
            <?php endif; ?>
            <div>
                <?php if ($clubName !== ''): ?>
                    <p class="sbfoot__name"><?= e($clubName) ?></p>
                <?php endif; ?>
                <?php if ($tagline !== ''): ?>
                    <p class="sbfoot__tagline"<?= $context->inline('tagline', 'Kort tekst') ?>><?= e($tagline) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasContact): ?>
            <div class="sbfoot__col">
                <p class="sbfoot__heading">Kontakt</p>
                <?php if ($address !== ''): ?>
                    <p class="sbfoot__line"><?= nl2br(e($address)) ?></p>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <p class="sbfoot__line">
                        <?php if ($phoneHref !== ''): ?>
                            <a href="<?= e($phoneHref) ?>"><?= e($phone) ?></a>
                        <?php else: ?>
                            <?= e($phone) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <p class="sbfoot__line">
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
            <nav class="sbfoot__col" aria-label="Footermenu">
                <p class="sbfoot__heading">Genveje</p>
                <ul class="sbfoot__links">
                    <?php foreach ($links as $link): ?>
                        <li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>

    <?php if ($copyright !== ''): ?>
        <p class="sbfoot__bottom"<?= $context->inline('copyright', 'Bundtekst') ?>><?= e($context->isInlineEditing() ? $copyrightRaw : $copyright) ?></p>
    <?php endif; ?>
</footer>
