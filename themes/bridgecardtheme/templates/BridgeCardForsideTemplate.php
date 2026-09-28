<?php
declare(strict_types=1);

/**
 * Forside til Bridge Card, bygget som designet i Figma.
 *
 * Navbar og footer er IKKE med. De er temaets globale blokke og ligger på
 * alle sider i forvejen. Felter, der ikke nævnes, får blokkens egen
 * standardværdi.
 */
final class BridgeCardForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'bridgecardtheme-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    public static function description(): string
    {
        return 'Hero, spilletider, spillehold, mesterpoint og en blå sektion. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        $c    = BridgeCardKit::COLORS;
        $text = 'It is a long established fact that a reader will be distracted by the readable content of It is a long established fact that a reader will be distracted by the readable content of';
        $card = static fn (string $icon, string $title, string $cardText = ''): array => [
            'icon'         => $icon,
            'title'        => $title,
            'text'         => $cardText !== '' ? $cardText : $text,
            'button_label' => 'Se turneringer',
            'page'         => 0,
            'url'          => '#',
        ];

        return [
            static::block('bridgecardtheme-hero'),
            static::block('bridgecardtheme-times'),

            // Hvid, tre kort, venstrestillet, turkis pynt.
            static::block('bridgecardtheme-cards'),

            // Turkis, fire kort, højrestillet, blå pynt.
            static::block('bridgecardtheme-cards', [
                'title' => 'Mesterpoint og point',
                'cards' => [
                    $card('Personer', 'Mesterpoint', 'Se klubbens medlemmer fordelt efter bronze, sølv, guld og samlede mesterpoint'),
                    $card('Liste', 'Rangliste'),
                    $card('Kalender', 'Bronzeudstilling'),
                    $card('Kalender', 'Handicap'),
                ],
            ], [
                'align'         => 'Højre',
                'bg_top'        => $c['teal'],
                'bg_bottom'     => $c['teal'],
                'decor_color'   => $c['blue'],
                'eyebrow_color' => $c['white'],
                'text_color'    => $c['white'],
                'title_size'    => 48,
            ]),

            static::block('bridgecardtheme-cta'),
        ];
    }
}
