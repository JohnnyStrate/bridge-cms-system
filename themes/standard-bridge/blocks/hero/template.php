<?php
/**
 * Template for standardbridge-hero.
 *
 * @var string        $title
 * @var RenderContext $context
 */
?>
<section class="block sbhero">
    <h1<?= $context->inline('title', 'Overskrift') ?>><?= e($title) ?></h1>
</section>
