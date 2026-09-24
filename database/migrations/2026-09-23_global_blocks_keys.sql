-- =====================================================================
--  Migration: nøgler på global_blocks
--
--  TIL EKSISTERENDE DATABASER. Nye installationer fra schema.sql har
--  det allerede.
--
--  HVAD ER PROBLEMET
--  global_blocks mangler primærnøgle, AUTO_INCREMENT og en unik nøgle på
--  slot. GlobalBlockRepository::save() bruger
--  INSERT ... ON DUPLICATE KEY UPDATE og er afhængig af dem.
--
--  Uden dem sker ét af to, afhængigt af serverens indstillinger:
--    - gemningen fejler med "Field 'id' doesn't have a default value", eller
--    - der oprettes en NY række hver gang i stedet for at opdatere den
--      gamle, så navbaren efterhånden findes i flere eksemplarer.
--
--  SÅDAN KØRER DU DEN
--  phpMyAdmin → vælg databasen → fanen SQL → indsæt hele filen → Kør.
--  Tag en eksport af databasen først.
--
--  Er navbaren allerede havnet i databasen flere gange, rydder
--  migrationen selv op og beholder den nyeste af hver slags. Derfor er
--  eksporten inden vigtig.
--
--  Fejler den med "Multiple primary key defined", er den kørt før, og der
--  er ikke mere at gøre.
-- =====================================================================

-- 1. Rækker, der er kommet ind med id = 0, får et rigtigt nummer.
--    Variabelnavnet er med vilje uden æ, ø og å: MariaDB afviser dem.
SET @next_id := (SELECT COALESCE(MAX(`id`), 0) FROM `global_blocks` WHERE `id` > 0);

UPDATE `global_blocks`
   SET `id` = (@next_id := @next_id + 1)
 WHERE `id` = 0;

-- 2. Dubletter fjernes, så den unikke nøgle kan sættes på.
--    En række slettes, hvis der findes en NYERE med samme slot.
--    Ved samme tidspunkt vinder den med det højeste id.
DELETE `gammel`
  FROM `global_blocks` AS `gammel`
  JOIN `global_blocks` AS `nyere`
    ON `nyere`.`slot` = `gammel`.`slot`
   AND (`nyere`.`updated_at` > `gammel`.`updated_at`
        OR (`nyere`.`updated_at` = `gammel`.`updated_at`
            AND `nyere`.`id` > `gammel`.`id`));

ALTER TABLE `global_blocks`
  MODIFY `id` INT UNSIGNED NOT NULL,
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_global_blocks_slot` (`slot`);

ALTER TABLE `global_blocks`
  MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
