-- =====================================================================
--  Bridge CMS — databaseskema
--
--  Opretter alle tabeller i en TOM database. Indeholder ingen data;
--  grunddata (de globale blokke) ligger i seed.sql.
--
--  Skabelonerne står IKKE i databasen. De ligger som kode i
--  /themes/<tema>/templates/
--  og følger derfor med i git.
--
--  NY INSTALLATION
--    1. Opret en tom database i phpMyAdmin (utf8mb4_unicode_ci).
--    2. Importér denne fil.
--    3. Importér seed.sql.
--    4. Kopiér include/database.example.php til include/database.php
--       og udfyld jeres egne adgangsoplysninger.
--
--  EKSISTERENDE DATABASE
--    Kør filerne i database/migrations/ i stedet. De ændrer en database,
--    der allerede har indhold, uden at slette noget.
--
--  ÆNDRER I STRUKTUREN
--    Opdatér denne fil i samme commit, og læg en migration i
--    database/migrations/, så de andres databaser kan følge med.
--
--  Testet på MariaDB 10.4 (XAMPP) og 10.11.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------
--  Sider
--
--  slug_path gør "samme adresse under samme forælder" umulig på
--  databaseniveau. En rod-side tæller som forælder 0.
--
--  fk_pages_parent har bevidst INGEN "ON UPDATE CASCADE": nyere MariaDB
--  afviser det på en kolonne, som slug_path er beregnet ud fra, og så
--  fejler hele importen. Et side-id ændres aldrig, så der er intet at
--  kaskadere.
-- ---------------------------------------------------------------------
CREATE TABLE `pages` (
  `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                    VARCHAR(255) NOT NULL,
  `slug`                     VARCHAR(255) NOT NULL,
  `parent_id`                INT UNSIGNED DEFAULT NULL,
  `status`                   ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `sort_order`               INT NOT NULL DEFAULT 0,
  -- Kun til dokumentation: hvilken skabelon siden blev lavet fra.
  -- Blokkene kopieres ned ved oprettelsen og er uafhængige bagefter.
  `created_from_template`    VARCHAR(100) DEFAULT NULL,
  `last_published_at`        TIMESTAMP NULL DEFAULT NULL,
  `created_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slug_path`                VARCHAR(320)
    GENERATED ALWAYS AS (CONCAT(COALESCE(`parent_id`, 0), '/', `slug`)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug_path` (`slug_path`),
  KEY `idx_pages_parent_order` (`parent_id`, `sort_order`),
  KEY `idx_pages_status` (`status`),
  CONSTRAINT `fk_pages_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `pages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blokkene på en side. Slettes sammen med siden.
CREATE TABLE `page_blocks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`    INT UNSIGNED NOT NULL,
  `block_type` VARCHAR(50) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `settings`   LONGTEXT NOT NULL CHECK (JSON_VALID(`settings`)),
  `styles`     LONGTEXT NOT NULL DEFAULT '{}' CHECK (JSON_VALID(`styles`)),
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blocks_page_order` (`page_id`, `sort_order`),
  KEY `idx_blocks_type` (`block_type`),
  CONSTRAINT `fk_blocks_page` FOREIGN KEY (`page_id`)
    REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Globale blokke (navbar og footer) — én pr. tema og slot
--
--  Hvert tema har sin egen navbar og footer. De gemmes side om side, så
--  man kan skifte tema og tilbage igen uden at miste noget. Hvilket tema
--  der vises, står i site_settings.active_theme.
--
--  `theme` er temaets mappenavn i /themes/, fx 'blaa-tema' eller 'tema1'.
--
--  Den unikke nøgle på (theme, slot) er IKKE til pynt:
--  GlobalBlockRepository::save() bruger INSERT ... ON DUPLICATE KEY UPDATE
--  og er afhængig af, at der højst findes én række pr. tema og slot.
-- ---------------------------------------------------------------------
CREATE TABLE `global_blocks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `theme`      VARCHAR(50) NOT NULL,
  `slot`       VARCHAR(50) NOT NULL,
  `block_type` VARCHAR(50) NOT NULL,
  `settings`   LONGTEXT NOT NULL CHECK (JSON_VALID(`settings`)),
  `styles`     LONGTEXT NOT NULL DEFAULT '{}' CHECK (JSON_VALID(`styles`)),
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_global_blocks_theme_slot` (`theme`, `slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Indstillinger for hele sitet, som nøgle/værdi
--
--  active_theme = mappenavnet på det tema, sitet bruger.
-- ---------------------------------------------------------------------
CREATE TABLE `site_settings` (
  `key`        VARCHAR(50) NOT NULL,
  `value`      TEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Gallerier
--
--  Billederne ligger som en JSON-liste af {"src", "alt", "caption"}.
--  Galleri-blokke peger på et galleri via settings.gallery_id.
-- ---------------------------------------------------------------------
CREATE TABLE `galleries` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `images`     LONGTEXT NOT NULL DEFAULT '[]' CHECK (JSON_VALID(`images`)),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_galleries_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
