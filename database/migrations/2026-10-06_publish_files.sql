-- =====================================================================
--  Migration: huskeliste til FTP-udgivelse
--
--  TIL EKSISTERENDE DATABASER. Nye installationer har det allerede.
--
--  HVAD ÆNDRES
--    Opretter tabellen publish_files. Den husker, hvilke filer der er
--    uploadet til webserveren, og et fingeraftryk af hver, så næste
--    udgivelse kun uploader det, der er ændret.
--
--    FTP-oplysningerne (host, bruger, kodeord, mappe) gemmes i den
--    eksisterende tabel site_settings. Dér skal intet ændres.
--
--  SÅDAN KØRER DU DEN
--  phpMyAdmin → vælg databasen → fanen SQL → indsæt hele filen → Kør.
--
--  Den kan køres flere gange uden at gå i stykker. Intet slettes.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `publish_files` (
  `path`        VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `hash`        CHAR(40) NOT NULL,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
