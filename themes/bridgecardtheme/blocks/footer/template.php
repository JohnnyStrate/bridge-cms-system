<?php
/**
 * Template for Bridge Card-footeren.
 *
 * @var string                           $timesTitle
 * @var array<int, array<string, mixed>> $times  index, day, time
 * @var string                           $linksTitle
 * @var array<int, array<string, mixed>> $links  index, label, href
 * @var string                           $infoTitle
 * @var array<int, array<string, mixed>> $info   index, text
 * @var string                           $cssVars
 * @var RenderContext                    $context
 */
?>
<?= BridgeCardKit::stylesheet($context) ?>
<footer class="block block--bridgecardtheme-footer"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="bcfoot__inner">
        <div class="bcfoot__col" data-bc-reveal="left">
            <h2 class="bcfoot__title"<?= $context->inline('times_title', 'Overskrift') ?>><?= e($timesTitle) ?></h2>
            <dl class="bcfoot__times">
                <?php foreach ($times as $row): ?>
                    <div>
                        <dt<?= $context->inlineRow('times', (int) $row['index'], 'day', 'Dag') ?>><?= e((string) ($row['day'] ?? '')) ?></dt>
                        <dd<?= $context->inlineRow('times', (int) $row['index'], 'time', 'Tid') ?>><?= e((string) ($row['time'] ?? '')) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <div class="bcfoot__col bcfoot__col--center" data-bc-reveal="bottom" style="--i:1">
            <h2 class="bcfoot__title"<?= $context->inline('links_title', 'Overskrift') ?>><?= e($linksTitle) ?></h2>
            <ul class="bcfoot__links">
                <?php foreach ($links as $link): ?>
                    <li><a href="<?= e($link['href']) ?>"<?= $context->inlineRow('links', (int) $link['index'], 'label', 'Link') ?>><?= e($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bcfoot__col bcfoot__col--right" data-bc-reveal="right" style="--i:2">
            <h2 class="bcfoot__title"<?= $context->inline('info_title', 'Overskrift') ?>><?= e($infoTitle) ?></h2>
            <?php foreach ($info as $row): ?>
                <p class="bcfoot__line"<?= $context->inlineRow('info', (int) $row['index'], 'text', 'Tekst') ?>><?= e((string) ($row['text'] ?? '')) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</footer>
<?= BridgeCardKit::revealScript($context) ?>
