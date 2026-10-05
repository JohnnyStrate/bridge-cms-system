<?php
declare(strict_types=1);

/**
 * Landingsside til Standard Bridge 2 — "Turneringer og resultater".
 *
 * En lille side med to billedlinks til eksterne resultatsystemer og en
 * fakta-boks om spilleholdet. Den er tænkt som ÉN side, som flere knapper
 * peger på: opret den én gang, og vælg den så under "Side" i navbarens
 * menupunkter og i "Spil bridge"-rækkerne på forsiden.
 *
 * Indholdet er almindelige blokke, så alt kan redigeres som på andre sider.
 */
final class Standard2LandingTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standard2-landing';
    }

    public static function name(): string
    {
        return 'Landingsside';
    }

    public static function description(): string
    {
        return 'Lille side med to links til eksterne resultater og fakta om spilleholdet.';
    }

    public static function sortOrder(): int
    {
        return 20;
    }

    public static function blocks(): array
    {
        return [
            static::block('standard2-cards', [
                'title' => 'Turneringer og resultater',
                'intro' => '',
                'rows'  => [
                    [
                        'title'      => '',
                        'text'       => 'Se spilleholdets turneringer og spilleaftener i det officielle resultatsystem.',
                        'link_label' => 'Turneringsoversigt',
                        'page'       => 0,
                        'url'        => '#',
                        'image'      => 'themes/standard2/assets/seturneringer.jpg',
                    ],
                    [
                        'title'      => '',
                        'text'       => 'Se spilleholdets aktuelle turnering direkte i resultatsystemet.',
                        'link_label' => 'Aktuelle turneringer',
                        'page'       => 0,
                        'url'        => '#',
                        'image'      => 'themes/standard2/assets/aktuelleturnering.jpg',
                    ],
                ],
            ], [
                'heading_style' => 'Stor tekst',
            ]),

            static::block('standard2-facts'),
        ];
    }
}
