<?php
/**
 * Template for Rangliste-blokken.
 *
 * @var string                $title
 * @var string                $titleAttr  Attributter til live-redigering (tom uden for editoren).
 * @var array<string, string> $labels     Kolonneoverskrifterne.
 * @var array<int, array{title: string, rows: array<int, array<string, string>>, hidden: int, skipped: int}> $groups
 * @var bool                  $editing
 * @var string                $cssVars
 *
 * OPBYGNING
 * Hver række er ét grid med to celler: en hovedcelle (navn og de tre
 * tal) og en Total-celle. Baggrunden sidder på cellerne, ikke på en
 * fælles boks, og rækkerne står uden mellemrum. Derfor ligner de to
 * spalter to sammenhængende bokse — og fordi navn og Total står i samme
 * række, flugter de altid, også når et langt navn brydes over to linjer.
 *
 * role-attributterne fortæller skærmlæsere, at det her er en tabel,
 * selvom den er bygget af div'er for at kunne have mellemrum mellem
 * spalterne.
 */
?>
<section class="block block--ranklist"<?= eAttr(['style' => $cssVars]) ?>>
    <div class="ranklist__inner">
        <?php if ($title !== '' || $titleAttr !== ''): ?>
            <h2 class="ranklist__title"<?= $titleAttr ?>><?= e($title) ?></h2>
        <?php endif; ?>

        <div class="ranklist__scroll">
            <div class="ranklist__table" role="table" aria-label="<?= e($title !== '' ? $title : 'Rangliste') ?>">

                <div class="ranklist__row ranklist__row--head" role="row">
                    <div class="ranklist__main">
                        <span class="ranklist__cell ranklist__cell--name" role="columnheader"><?= e($labels['name']) ?></span>
                        <span class="ranklist__cell" role="columnheader"><?= e($labels['bronze']) ?></span>
                        <span class="ranklist__cell" role="columnheader"><?= e($labels['silver']) ?></span>
                        <span class="ranklist__cell" role="columnheader"><?= e($labels['gold']) ?></span>
                    </div>
                    <div class="ranklist__total">
                        <span class="ranklist__cell" role="columnheader"><?= e($labels['total']) ?></span>
                    </div>
                </div>

                <?php foreach ($groups as $group): ?>
                    <div class="ranklist__group">
                        <?php if ($group['title'] !== ''): ?>
                            <h3 class="ranklist__subtitle"><?= e($group['title']) ?></h3>
                        <?php endif; ?>

                        <?php if ($group['rows'] === []): ?>
                            <?php /* Kun i editoren — på siden springes tomme grupper over. */ ?>
                            <p class="ranklist__note">Ingen spillere endnu. Skriv én pr. linje: Navn; Bronze; Sølv; Guld</p>
                        <?php else: ?>
                            <div class="ranklist__body" role="rowgroup" aria-label="<?= e($group['title']) ?>">
                                <?php foreach ($group['rows'] as $row): ?>
                                    <div class="ranklist__row" role="row">
                                        <div class="ranklist__main">
                                            <span class="ranklist__cell ranklist__cell--name" role="cell"><?= e($row['name']) ?></span>
                                            <span class="ranklist__cell ranklist__cell--number" role="cell"><?= e($row['bronze']) ?></span>
                                            <span class="ranklist__cell ranklist__cell--number" role="cell"><?= e($row['silver']) ?></span>
                                            <span class="ranklist__cell ranklist__cell--number" role="cell"><?= e($row['gold']) ?></span>
                                        </div>
                                        <div class="ranklist__total">
                                            <span class="ranklist__cell ranklist__cell--pts" role="cell"><?= e($row['total']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($editing && $group['hidden'] > 0): ?>
                            <p class="ranklist__note">
                                + <?= (int) $group['hidden'] ?> flere — alle vises på den færdige side
                            </p>
                        <?php endif; ?>

                        <?php if ($editing && $group['skipped'] > 0): ?>
                            <p class="ranklist__note ranklist__note--warning">
                                <?= (int) $group['skipped'] ?> linje(r) kunne ikke læses og vises ikke.
                                Formatet er: Navn; Bronze; Sølv; Guld
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</section>
