<?php
/**
 * Template for Footer-blokken.
 *
 * @var string                            $clubName
 * @var string                            $address
 * @var string                            $email      Til visning.
 * @var string                            $emailHref  Tom, hvis ugyldig.
 * @var string                            $phone      Til visning.
 * @var string                            $phoneHref  Tom, hvis tom.
 * @var array<int, array<string, string>> $links
 * @var string                            $copyright
 * @var string    
 * @var RenderContext                        $cssVars
 *
 * 
 * Templaten træffer ingen beslutninger om adresser — den viser kun det,
 * FooterBlock::render() allerede har regnet ud.
 */
$context->isInlineEditing();
?>
<footer class="block block--footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="footer__inner">

        <div class="footer__col">
            <?php if ($clubName !== ''): ?>
                <p class="footer__name" <?= $context->inline('club_name', 'Klubbens navn') ?>><?= e($clubName) ?></p>
            <?php endif; ?>

            <?php if ($address !== ''): ?>
                <p class="footer__line" <?= $context->inline('address', 'Adresse') ?>><?= e($address) ?></p>
            <?php endif; ?>

            <?php if ($phone !== ''): ?>
                <p class="footer__line">
                    <?php if ($phoneHref !== ''): ?>
                        <a href="<?= e($phoneHref) ?>" <?= $context->inline('phone', 'Telefon') ?>><?= e($phone) ?></a>
                    <?php else: ?>
                        <?= e($phone) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if ($email !== ''): ?>
                <p class="footer__line">
                    <?php if ($emailHref !== ''): ?>
                        <a <?= $context->inline('email', 'E-mail') ?> href="<?= e($emailHref) ?>"><?= e($email) ?></a>
                    <?php else: ?>
                        <?= e($email) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($links !== []): ?>
            <nav class="footer__col" aria-label="Footermenu">
                <ul class="footer__links">
                    <?php foreach ($links as $link): ?>
                        <li>
                            <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>

    <?php if ($copyright !== ''): ?>
        <p  <?= $context->inline('copyright', 'Bundtekst (skriv {år} for årstallet)') ?> class="footer__bottom"><?= e($copyright) ?></p>
    <?php endif; ?>
</footer>