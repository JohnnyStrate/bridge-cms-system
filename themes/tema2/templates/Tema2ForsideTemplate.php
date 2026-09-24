<?php
declare(strict_types=1);

/**
 * Forside til tema 2.
 *
 * Navbaren er IKKE med — den er temaets globale blok og ligger på alle
 * sider i forvejen. Hero'en står øverst, så navbaren lægger sig oven på
 * billedet.
 *
 * Flere sektioner skrives ind her, efterhånden som de bliver bygget.
 */
final class Tema2ForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'tema2-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Forside til tema 2 med fotohero. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        return [
            // Felter, der ikke nævnes, får blokkens egen standardværdi
            // (dummy-teksten og billedet i themes/tema2/assets/).
            static::block('tema2-hero'),
            static::block('tema2-textimage'),
            static::block('tema2-cards'),
            static::block('tema2-callout'),

            // Samme kort-blok igen, nu hvid og med tider og steder.
            static::block('tema2-cards', [
                'eyebrow' => 'Tider og steder',
                'title'   => 'Spil bridge',
                'cards'   => [
                    [
                        'icon'         => 'Ur',
                        'title'        => 'Klubaften',
                        'text'         => 'Skriv hvilken ugedag og hvilket klokkeslæt I spiller, fx mandag kl. 18.45.',
                        'button_label' => 'Se program',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'icon'         => 'Sted',
                        'title'        => 'Åbent hus',
                        'text'         => 'Fortæl hvor I mødes — adresse, lokale og hvordan man finder vej.',
                        'button_label' => 'Se adresse',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                    [
                        'icon'         => 'Kalender',
                        'title'        => 'Sommer- og sølvturneringer',
                        'text'         => 'Skriv om sæsonens turneringer, og hvornår man skal tilmelde sig.',
                        'button_label' => 'Se turneringer',
                        'page'         => 0,
                        'url'          => '#',
                    ],
                ],
            ], [
                'background_color' => '#ffffff',
                'title_suits'      => 'Vis',
            ]),

            static::block('tema2-quote'),
        ];
    }
}
