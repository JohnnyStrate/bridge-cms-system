# Databasen

## Ny installation

1. Opret en tom database i phpMyAdmin med sammenligning `utf8mb4_unicode_ci`.
2. Importér `schema.sql` (tabellerne).
3. Importér `seed.sql` (det aktive tema og dets navbar og footer).
4. Kopiér `include/database.example.php` til `include/database.php`, og
   udfyld jeres egne adgangsoplysninger. Den fil skal aldrig i git.

Projektet skulle nu kunne åbnes på `admin/index.php`.

## Har du allerede en database?

Kør filerne i `migrations/` i stedet — én ad gangen, ældste først.
De ændrer en eksisterende database uden at slette indhold.

| Rækkefølge | Fil | Hvorfor |
|---|---|---|
| 1 | `2026-09-23_global_blocks_keys.sql` | Uden den kan navbar og footer ikke gemmes rigtigt. |
| 2 | `2026-09-23_templates_i_kode.sql` | Uden den kan der ikke oprettes sider fra en skabelon. |
| 3 | `2026-09-24_temaer.sql` | Navbar og footer pr. tema + tabellen `site_settings`. Uden den stopper admin med en besked om at køre den. |

Nummer 3 kan køres flere gange uden at gå i stykker.

## Når I ændrer strukturen

Tre ting i samme commit, ellers går det galt for de andre:

1. Ret `schema.sql`, så nye installationer får den nye struktur.
2. Læg en migration i `migrations/` med dagens dato i navnet, så de
   eksisterende databaser kan følge med.
3. Skriv i pull requesten, at der skal køres en migration.

## Hvad ligger hvor

| Tabel             | Indhold                                          |
|-------------------|--------------------------------------------------|
| `pages`           | Siderne og deres placering i sidetræet           |
| `page_blocks`     | Blokkene på hver side                            |
| `global_blocks`   | Navbar og footer — én af hver pr. tema           |
| `site_settings`   | Sitets indstillinger, fx `active_theme`          |
| `galleries`       | Billedgallerier, som galleri-blokken peger på    |

Temaer og skabeloner ligger ikke i databasen. De er kode i `themes/`, én
mappe pr. tema, og opdages automatisk. Databasen gemmer kun, hvilket tema
der er aktivt, og hvert temas navbar og footer.

Indholdet i `settings` og `styles` er JSON og skal matche blokkenes
skemaer i `themes/<tema>/blocks/` og `blocks/faelles/`. Passer et feltnavn ikke, bruges blokkens
standardværdi i stedet.
