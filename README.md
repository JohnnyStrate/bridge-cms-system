# Bridge CMS

Et CMS i ren PHP til bridgeklubber. Man vælger et tema, bygger sider af
blokke i en visuel editor og udgiver sitet som statisk HTML.

PHP 8.1+, MariaDB 10.4+ (XAMPP). Ingen frameworks, ingen composer.

## Kom i gang

1. Opret en tom database i phpMyAdmin (`utf8mb4_unicode_ci`).
2. Importér `database/schema.sql` og derefter `database/seed.sql`.
3. Kopiér `include/database.example.php` til `include/database.php` og
   udfyld jeres adgangsoplysninger. Filen må ikke i git.
4. Åbn `admin/` i browseren.

Har du en ældre database, så se `database/README.md`.

## Hvor ligger hvad

| Mappe | Indhold |
|---|---|
| `admin/` | Adminpanelet: sider, editor, tema, gallerier, udgiv |
| `core/` | Registre, rendering, validering, eksport |
| `repositories/` | Al SQL |
| `themes/<tema>/` | Et tema: `<Navn>Theme.php`, `blocks/`, `templates/`, `assets/` |
| `blocks/faelles/` | Blokke, alle temaer kan bruge (billede, tekst, galleri) |
| `database/` | Skema, grunddata og migrationer |

## Nyt tema

1. Opret `themes/mit-tema/MitTemaTheme.php` (kopiér et eksisterende tema).
2. Byg blokkene i `themes/mit-tema/blocks/` med temaet foran typen,
   fx `mit-tema-hero`, og skriv dem i temaets `blocks()`.
3. Navbar og footer skrives i `globals()`.
4. Skabeloner lægges i `themes/mit-tema/templates/`.

Ingen fælles filer skal rettes, og der skal ikke køres SQL. Temaet dukker op
under "Tema" i admin.

## Udgiv til et webhotel (FTP)

Websitet bygges som statiske filer i `export/` og kan lægges op på et
hvilket som helst webhotel med FTP-adgang. CMS'et kører stadig kun lokalt.

1. Kør `database/migrations/2026-10-06_publish_files.sql` i phpMyAdmin
   (nye installationer har tabellen allerede).
2. Åbn **Server (FTP)** i admin og udfyld adresse, bruger, kodeord og mappe
   (ofte `public_html`). Lad **Krypteret forbindelse (FTPS)** stå til.
   Tryk **Test forbindelse**, før du gemmer.
3. Gå til **Udgiv** og tryk **Byg og upload til serveren**.

Efter første gang uploades kun nye og ændrede filer. Filer, der ikke er med
mere, slettes på serveren, men kun dem CMS'et selv har lagt derop. Er der
rettet noget direkte på serveren, så brug **Upload alt forfra**.

Kodeordet gemmes i databasen på jeres egen computer, aldrig i git. Giver
Test forbindelse beskeden om, at FTP-udvidelsen er slået fra: åbn `php.ini`
(XAMPP: Config ved Apache), fjern `;` foran `extension=ftp`, og genstart
Apache.
