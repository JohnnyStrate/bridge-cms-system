<?php
declare(strict_types=1);

/**
 * Klubforside: hero og velkomstsektion.
 *
 * Den skabelon, der før lå som rækker i page_templates og
 * template_blocks. Indholdet er det samme; det står bare her nu, hvor
 * det kan læses, kommenteres og følge med i git.
 *
 * Navbar og footer er IKKE med. De er globale blokke og ligger på alle
 * sider i forvejen, uanset hvilken skabelon siden er lavet fra.
 */
final class tema1Template extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'Tema forside';
    }

    public static function name(): string
    {
        return 'Tema forside';
    }

    public static function description(): string
    {
        return 'Forside med hero og velkomstsektion. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            static::block('hero', [
                'title'    => 'Din klubs navn',
                'address'  => 'Vejnavn 1, 1234 By',
                'phone'    => '+45 00 00 00 00',
                'bg_image' => 'assets/demo/hero-placeholder.jpg',
            ]),

            // Felter, der ikke nævnes her, får blokkens egen
            // standardværdi. Derfor står punkterne i listen ikke med.
            static::block('welcome', [
                'title'      => 'Velkommen',
                'intro'      => 'Skriv en kort introduktion til jeres klub her.',
                'list_title' => 'Vi tilbyder:',
            ]),
        ];
    }
}
