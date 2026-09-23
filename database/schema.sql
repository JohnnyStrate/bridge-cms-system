-- =====================================================================
--  Bridge CMS — databaseskema
--
--  Opretter alle tabeller i en TOM database. Indeholder ingen data;
--  grunddata (skabelonen og de globale blokke) ligger i seed.sql.
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
--  Skabeloner, man kan oprette en side ud fra
-- ---------------------------------------------------------------------
CREATE TABLE `page_templates` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100) NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `thumbnail`   VARCHAR(255) DEFAULT NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_templates_slug` (`slug`),
  KEY `idx_templates_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blokkene i en skabelon. Kopieres ned på siden, når den oprettes.
CREATE TABLE `template_blocks` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id`      INT UNSIGNED NOT NULL,
  `block_type`       VARCHAR(50) NOT NULL,
  `sort_order`       INT NOT NULL DEFAULT 0,
  `default_settings` LONGTEXT NOT NULL CHECK (JSON_VALID(`default_settings`)),
  `default_styles`   LONGTEXT NOT NULL DEFAULT '{}' CHECK (JSON_VALID(`default_styles`)),
  PRIMARY KEY (`id`),
  KEY `idx_tblocks_template_order` (`template_id`, `sort_order`),
  CONSTRAINT `fk_tblocks_template` FOREIGN KEY (`template_id`)
    REFERENCES `page_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_from_template_id` INT UNSIGNED DEFAULT NULL,
  `last_published_at`        TIMESTAMP NULL DEFAULT NULL,
  `created_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slug_path`                VARCHAR(320)
    GENERATED ALWAYS AS (CONCAT(COALESCE(`parent_id`, 0), '/', `slug`)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug_path` (`slug_path`),
  KEY `idx_pages_parent_order` (`parent_id`, `sort_order`),
  KEY `idx_pages_status` (`status`),
  KEY `fk_pages_template` (`created_from_template_id`),
  CONSTRAINT `fk_pages_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `pages` (`id`),
  CONSTRAINT `fk_pages_template` FOREIGN KEY (`created_from_template_id`)
    REFERENCES `page_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
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
--  Globale blokke (navbar og footer), fælles for alle sider
--
--  Primærnøglen og den unikke nøgle på slot er IKKE til pynt:
--  GlobalBlockRepository::save() bruger
--  INSERT ... ON DUPLICATE KEY UPDATE og er afhængig af, at der højst
--  findes én række pr. slot. Uden dem fejler det at gemme navbar og
--  footer med "Field 'id' doesn't have a default value".
-- ---------------------------------------------------------------------
CREATE TABLE `global_blocks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slot`       VARCHAR(50) NOT NULL,
  `block_type` VARCHAR(50) NOT NULL,
  `settings`   LONGTEXT NOT NULL CHECK (JSON_VALID(`settings`)),
  `styles`     LONGTEXT NOT NULL DEFAULT '{}' CHECK (JSON_VALID(`styles`)),
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_global_blocks_slot` (`slot`)
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
