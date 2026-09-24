# Databasen

## Ny installation

1. Opret en tom database i phpMyAdmin med sammenligning `utf8mb4_unicode_ci`.
2. Importér `schema.sql` (tabellerne).
3. Importér `seed.sql` (skabelonen og de globale blokke).
4. Kopiér `include/database.example.php` til `include/database.php`, og
   udfyld jeres egne adgangsoplysninger. Den fil skal aldrig i git.

Projektet skulle nu kunne åbnes på `admin/index.php`.

## Har du allerede en database?

Kør filerne i `migrations/` i stedet — én ad gangen, ældste først.
De ændrer en eksisterende database uden at slette indhold.

**Alle skal køre begge migrationer fra 23. september**, i den rækkefølge
filnavnene står. Uden `global_blocks_keys` kan navbar og footer ikke
gemmes rigtigt, og uden `templates_i_kode` kan der ikke oprettes sider
fra en skabelon.

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
| `global_blocks`   | Navbar og footer — fælles for alle sider         |
| `galleries`       | Billedgallerier, som galleri-blokken peger på    |

Skabelonerne ligger ikke i databasen. De er kode i `templates/`, én mappe
pr. skabelon, og opdages automatisk — en ny skabelon kræver derfor ingen
SQL og ingen ændring i fælles filer.

Indholdet i `settings` og `styles` er JSON og skal matche blokkenes
skemaer i `blocks/`. Passer et feltnavn ikke, bruges blokkens
standardværdi i stedet.
