-- =====================================================================
--  Bridge CMS — grunddata
--
--  Importeres EFTER schema.sql. Giver en ny installation:
--    - skabelonen "Klubforside" med hero og velkomstsektion
--    - en navbar og en footer med pladsholdertekst
--
--  Ingen sider, blokke eller gallerier: dem opretter man selv i admin.
--  Ingen uploadede billeder, da mappen uploads/ ikke ligger i git.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO `page_templates` (`id`, `slug`, `name`, `description`, `thumbnail`, `sort_order`, `is_active`) VALUES
(1, 'standard-klub', 'Klubforside',
 'Forside med hero og velkomstsektion. Alt indhold kan overskrives.',
 NULL, 10, 1);

-- Felterne skal matche blokkenes skemaer i blocks/. Gør de ikke det,
-- falder værdien tilbage til blokkens standard, og skabelonen ser tom ud.
INSERT INTO `template_blocks` (`template_id`, `block_type`, `sort_order`, `default_settings`, `default_styles`) VALUES
(1, 'hero', 10,
 '{"title": "Din klubs navn", "address": "Vejnavn 1, 1234 By", "phone": "+45 00 00 00 00", "bg_image": "assets/demo/hero-placeholder.jpg"}',
 '{}'),
(1, 'welcome', 20,
 '{"title": "Velkommen", "intro": "Skriv en kort introduktion til jeres klub her.", "list_title": "Vi tilbyder:", "items": [{"text": "Første punkt"}, {"text": "Andet punkt"}, {"text": "Tredje punkt"}], "footer_text": ""}',
 '{}');

-- Links med "page": 0 peger ikke på en side endnu. Vælg sider i editoren,
-- når siderne er oprettet.
INSERT INTO `global_blocks` (`slot`, `block_type`, `settings`, `styles`, `is_visible`) VALUES
('header', 'navbar',
 '{"logo": "", "logo_alt": "", "links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}]}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "link_size": 16, "font_family": "Jost"}', 1),
('footer', 'footer',
 '{"club_name": "Din klubs navn", "address": "Vejnavn 1, 1234 By", "email": "info@klub.dk", "phone": "+45 00 00 00 00", "links": [{"label": "Forside", "page": 0, "url": "#"}, {"label": "Kontakt", "page": 0, "url": "#"}], "copyright": "© {år} Din klub"}',
 '{"background_color": "#1e3a8a", "text_color": "#ffffff", "text_size": 15, "font_family": "Jost", "text_align": "left"}', 1);
