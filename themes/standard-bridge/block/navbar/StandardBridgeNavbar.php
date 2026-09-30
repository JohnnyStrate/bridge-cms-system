<?php declare(strict_types=1);
final class StandardBridgeNavbarBlock extends AbstractBlock{

public static funtion  type(): string {
    return 'standardbridge-navbar';
}
public static function label(): string {
    return 'standardbridge-navbar';
}
public static function getSchema(){
    return ['links' => [
        'type'=> 'repeater',
        'label' => 'menupunkter',
        'max_rows' => 30,
        'fields' => [
            'label' => [
                'type' => 'text',
                'label' => 'Tekst',
                'default' => '',
            ],
            'page' => [
                'type' => 'page',
                'label' => 'side',
                'default' => 0
            ],
            'url' => [
                'type' => 'url',
                'label'=> '',

            ],
        ],
        'default' => [
            ['label' => 'Forside', 'type' => 'page', 0, 'url' => '#' ],
            ['label' => 'Om Klubben', 'type' => 0, 'url' => '#'],
            ['label' => 'Tunering', 'type' => 0, 'url' => '#'],
            ['label' => 'Kontakt', 'type' => 0, 'url' => '#'],
        ],
        'cta_label' =>[
            'type' => 'text',
        '   label' => 'knap:text',
        'placeholder' => 'Tom ingen knap',
        'default' => 'Bliv medlem',

    ],
    'cta_page'[
        'type' => 'page',
        'label' => 'knap side',
        'default' => 0,
    
    ],
    'cta_url' =>[
        'type'   => 'url',
         'label'  => 'Knap: ekstern adresse',
        'placeholder' => 'Indsæt link',
                'default'     => '',
    ],
    ] 
}
};
