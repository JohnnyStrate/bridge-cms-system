-- =====================================================================
--  Bridge CMS — grunddata
--
--  Importeres EFTER schema.sql. Giver en ny installation en navbar og
--  en footer med pladsholdertekst.
--
--  Skabelonerne står ikke her: de ligger som kode i /templates/.
--
--  Ingen sider, blokke eller gallerier: dem opretter man selv i admin.
--  Ingen uploadede billeder, da mappen uploads/ ikke ligger i git.
-- =====================================================================

SET NAMES utf8mb4;

-- Links med "page": 0 peger ikke på en side endnu. Vælg sider i editoren,
-- når siderne er oprettet.
INSERT INTO `global_blocks` (`slot`, `block_type`, `settings`, `styles`, `is_visible`) VALUES
('header', 'navbar',
 '{"logo": "", "logo_alt": "", "links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}]}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "link_size": 16, "font_family": "Jost"}', 1),
('footer', 'footer',
 '{"club_name": "Din klubs navn", "address": "Vejnavn 1, 1234 By", "email": "info@klub.dk", "phone": "+45 00 00 00 00", "links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}], "copyright": "© {år} Din klub"}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "text_size": 15, "font_family": "Jost", "text_align": "left"}', 1);
