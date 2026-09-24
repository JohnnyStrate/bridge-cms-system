-- =====================================================================
--  Migration: skabeloner flyttet fra databasen til kode
--
--  Skabelonerne ligger nu i /templates/ som PHP-filer, så de følger med
--  i git i stedet for kun at findes i den enkeltes database. Derfor:
--
--    - pages.created_from_template_id (et tal) bliver til
--      pages.created_from_template (skabelonens navn, fx 'klubforside').
--    - page_templates og template_blocks bruges ikke længere og fjernes.
--
--  Kolonnen er kun til dokumentation: ingen kode slår op i den. Blokkene
--  kopieres ned på siden ved oprettelsen og er uafhængige bagefter.
--
--  SÅDAN KØRER DU DEN
--  phpMyAdmin → vælg databasen → fanen SQL → indsæt hele filen → Kør.
--  Tag en eksport af databasen først.
--
--  Kør den EFTER 2026-09-23_global_blocks_keys.sql.
-- =====================================================================

-- 1. Fremmednøglen skal væk, før tabellerne kan fjernes.
ALTER TABLE `pages`
  DROP FOREIGN KEY `fk_pages_template`;

ALTER TABLE `pages`
  DROP INDEX `fk_pages_template`;

-- 2. Kolonnen skal rumme et navn i stedet for et tal.
ALTER TABLE `pages`
  CHANGE `created_from_template_id` `created_from_template` VARCHAR(100) DEFAULT NULL;

-- 3. De sider, der blev lavet fra den gamle skabelon, får dens nye navn.
--    Står der et tal, vi ikke kender, ryddes feltet: siden virker
--    uændret, for blokkene ligger i page_blocks.
UPDATE `pages`
   SET `created_from_template` = 'klubforside'
 WHERE `created_from_template` = '1';

UPDATE `pages`
   SET `created_from_template` = NULL
 WHERE `created_from_template` REGEXP '^[0-9]+$';

-- 4. Tabellerne er nu tomme for mening. template_blocks først, fordi den
--    peger på page_templates.
DROP TABLE IF EXISTS `template_blocks`;
DROP TABLE IF EXISTS `page_templates`;
