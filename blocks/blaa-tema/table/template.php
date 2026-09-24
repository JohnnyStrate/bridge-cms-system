<?php
/**
 * Template for Tabel-blokken.
 *
 * @var string                                                   $title
 * @var string                                                   $titleAttr Attributter til live-redigering.
 * @var array<int, string>                                       $columns
 * @var array<int, array<int, array{text: string, href: string}>> $rows
 * @var int                                                      $hidden    Rækker skjult i editoren.
 * @var string                                                   $note
 * @var bool                                                     $editing
 * @var string                                                   $cssVars
 *
 * Hver række er ét grid med lige så mange spalter, som der er kolonner.
 * Baggrunden sidder på cellerne, og rækkerne står tæt, så cellerne i
 * samme kolonne tilsammen ligner én boks. Afstanden mellem spalterne er
 * det, der deler dem op i separate bokse. Fordi en række er ét grid,
 * flugter cellerne på tværs, også når en tekst brydes over to linjer.
 */
?>
<section class="block block--table"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="table__inner">
        <?php if ($title !== '' || $titleAttr !== ''): ?>
            <h2 class="table__title"<?= $titleAttr ?>><?= e($title) ?></h2>
        <?php endif; ?>

        <div class="table__scroll">
            <div class="table__grid" role="table" aria-label="<?= e($title !== '' ? $title : 'Tabel') ?>">
                <div class="table__row table__row--head" role="row">
                    <?php foreach ($columns as $column): ?>
                        <span class="table__cell" role="columnheader"><?= e($column) ?></span>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($rows as $row): ?>
                    <div class="table__row" role="row">
                        <?php foreach ($row as $cell): ?>
                            <span class="table__cell" role="cell">
                                <?php if ($cell['href'] !== ''): ?>
                                    <a class="table__link" href="<?= e($cell['href']) ?>"><?= e($cell['text']) ?></a>
                                <?php else: ?>
                                    <?= e($cell['text']) ?>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($editing && $rows === []): ?>
            <p class="table__editor-note">Ingen rækker endnu. Skriv én pr. linje, fx: Klør Syv; Tirsdag; 13:00</p>
        <?php endif; ?>

        <?php if ($editing && $hidden > 0): ?>
            <p class="table__editor-note">+ <?= (int) $hidden ?> rækker mere — alle vises på den færdige side</p>
        <?php endif; ?>

        <?php if ($note !== ''): ?>
            <p class="table__note"><?= nl2br(e($note)) ?></p>
        <?php endif; ?>
    </div>
</section>
