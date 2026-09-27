<?php
declare(strict_types=1);

/**
 * Forside til Bridge Card, bygget som mockuppen.
 *
 * Navbar og footer er IKKE med. De er temaets globale blokke og ligger på
 * alle sider i forvejen.
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
        return 'Hero, spilletider, spillehold, point og en blå sektion. Alt indhold kan overskrives.';
    }

    public static function sortOrder(): int
    {
        return 10;
    }

    public static function blocks(): array
    {
        $text = 'It is a long established fact that a reader will be distracted by the readable content of';
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

            // Hvid, tre kort, venstrestillet.
            static::block('bridgecardtheme-cards'),

            // Turkis, fire kort, højrestillet.
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
                'bg_top'        => '#70c3b4',
                'bg_bottom'     => '#70c3b4',
                'eyebrow_color' => '#ffffff',
                'text_color'    => '#1f4497',
            ]),

            static::block('bridgecardtheme-cta'),
        ];
    }
}
