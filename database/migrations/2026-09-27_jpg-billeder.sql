-- =====================================================================
--  Migration: dummybillederne er skiftet fra PNG til JPG
--
--  TIL EKSISTERENDE DATABASER. Nye installationer har det allerede.
--
--  HVAD ÆNDRES
--    themes/tema2/assets/heroimage.png    → heroimage.jpg
--    themes/tema2/assets/welcomeimage.png → welcomeimage.jpg
--
--  De gamle PNG-filer var 6000×4000 pixel. De nye JPG-filer er 2000 px
--  brede og en brøkdel så tunge — siderne bliver hurtigere at hente.
--
--  Sider og navbar/footer, der allerede er oprettet, har stien til PNG'en
--  gemt i databasen. Den skiftes her til JPG'en. Intet andet røres.
--
--  SÅDAN KØRER DU DEN
--  phpMyAdmin → vælg databasen → fanen SQL → indsæt hele filen → Kør.
--  Slet FØRST de to PNG-filer bagefter — ikke før.
--
--  Den kan køres flere gange uden at gå i stykker.
-- =====================================================================

UPDATE `page_blocks`
   SET `settings` = REPLACE(REPLACE(`settings`,
       'themes/tema2/assets/heroimage.png',    'themes/tema2/assets/heroimage.jpg'),
       'themes/tema2/assets/welcomeimage.png', 'themes/tema2/assets/welcomeimage.jpg')
 WHERE `settings` LIKE '%themes/tema2/assets/%image.png%';

UPDATE `global_blocks`
   SET `settings` = REPLACE(REPLACE(`settings`,
       'themes/tema2/assets/heroimage.png',    'themes/tema2/assets/heroimage.jpg'),
       'themes/tema2/assets/welcomeimage.png', 'themes/tema2/assets/welcomeimage.jpg')
 WHERE `settings` LIKE '%themes/tema2/assets/%image.png%';
