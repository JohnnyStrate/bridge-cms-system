-- =====================================================================
--  Migration: temaer — navbar og footer pr. tema
--
--  TIL EKSISTERENDE DATABASER. Nye installationer fra schema.sql har
--  det allerede.
--
--  HVAD ÆNDRES
--    - global_blocks får kolonnen `theme`. Hvert tema har sin egen navbar
--      og footer, og de gemmes side om side, så man kan skifte tema og
--      tilbage igen uden at miste noget.
--    - Den unikke nøgle går fra (slot) til (theme, slot).
--    - Ny tabel site_settings med det aktive tema.
--
--  Det, der står i global_blocks i dag, bliver liggende. Rækker med en
--  tema1-type ('tema1-navbar') flyttes til tema1, resten til det blå tema.
--  Det aktive tema bliver det, jeres nuværende navbar hører til.
--
--  SÅDAN KØRER DU DEN
--  phpMyAdmin → vælg databasen → fanen SQL → indsæt hele filen → Kør.
--  Tag en eksport af databasen først.
--
--  Kør den EFTER 2026-09-23_global_blocks_keys.sql og
--  2026-09-23_templates_i_kode.sql.
--
--  Den kan køres flere gange uden at gå i stykker.
-- =====================================================================

-- 1. Kolonnen. Standarden bruges kun til de rækker, der findes nu.
ALTER TABLE `global_blocks`
  ADD COLUMN IF NOT EXISTS `theme` VARCHAR(50) NOT NULL DEFAULT 'blaa-tema' AFTER `id`;

UPDATE `global_blocks`
   SET `theme` = 'tema1'
 WHERE `block_type` LIKE 'tema1-%';

-- 2. Én række pr. tema og slot i stedet for én pr. slot.
ALTER TABLE `global_blocks`
  DROP INDEX IF EXISTS `uq_global_blocks_slot`,
  ADD UNIQUE KEY IF NOT EXISTS `uq_global_blocks_theme_slot` (`theme`, `slot`);

-- Nye rækker skal altid sige, hvilket tema de hører til.
ALTER TABLE `global_blocks`
  ALTER COLUMN `theme` DROP DEFAULT;

-- 3. Indstillinger for hele sitet.
CREATE TABLE IF NOT EXISTS `site_settings` (
  `key`        VARCHAR(50) NOT NULL,
  `value`      TEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Det aktive tema er det, den nuværende navbar hører til.
--    Står der allerede et, røres det ikke.
INSERT IGNORE INTO `site_settings` (`key`, `value`)
SELECT 'active_theme',
       COALESCE(
           (SELECT `theme` FROM `global_blocks` WHERE `slot` = 'header' LIMIT 1),
           'blaa-tema'
       );
