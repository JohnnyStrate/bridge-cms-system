-- =====================================================================
--  Bridge CMS — grunddata
--
--  Importeres EFTER schema.sql. Giver en ny installation det blå tema som
--  aktivt tema, med en navbar og en footer med pladsholdertekst.
--
--  Andre temaers navbar og footer oprettes automatisk med temaets
--  dummy-indhold, første gang temaet vælges under "Tema" i admin.
--
--  Skabelonerne står ikke her: de ligger som kode i /themes/<tema>/templates/.
--
--  Ingen sider, blokke eller gallerier: dem opretter man selv i admin.
--  Ingen uploadede billeder, da mappen uploads/ ikke ligger i git.
-- =====================================================================

SET NAMES utf8mb4;

-- Links med "page": 0 peger ikke på en side endnu. Vælg sider i editoren,
-- når siderne er oprettet.
-- Klubbens oplysninger (Indstillinger i admin). Mangler en af dem, bruger
-- koden en pladsholder, så rækkerne her er kun et udgangspunkt.
INSERT INTO `site_settings` (`key`, `value`) VALUES
('active_theme', 'blaa-tema'),
('club_name',    'Din Bridgeklub'),
('club_address', 'Vejnavn 1\n1234 By'),
('club_phone',   '+45 00 00 00 00'),
('club_email',   'info@dinklub.dk'),
('club_logo',    '');

INSERT INTO `global_blocks` (`theme`, `slot`, `block_type`, `settings`, `styles`, `is_visible`) VALUES
('blaa-tema', 'header', 'navbar',
 '{"links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}]}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "link_size": 16, "font_family": "Jost"}', 1),
('blaa-tema', 'footer', 'footer',
 '{"links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}], "copyright": "© {år} {klub}"}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "text_size": 15, "font_family": "Jost", "text_align": "left"}', 1);
