<?php
declare(strict_types=1);

/**
 * Fakta-boks til Standard Bridge 2 — fx "Om spilleholdet".
 *
 * En overskrift (standard: store versaler uden boks) og under den en
 * hvid boks med op til seks små fakta i to kolonner:
 *
 *     HOVEDKLUB              KLUBNUMMER
 *     Din Bridgeklub         0000 / 0
 *
 *     AKTIVITET              SPILLESTED
 *     Turneringsbridge       Klubhuset
 *
 *                  Din Bridgeklub        <- lille underskrift med streg
 *                  ──────────────
 *
 * Underskriften er som standard klubbens navn fra Indstillinger ({klub}).
 */
final class Standard2FactsBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-facts';
    }

    public static function label(): string
    {
        return 'Fakta-boks — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => 'Om spilleholdet',
                'max'     => 60,
            ],
            'facts' => [
                'type'     => 'repeater',
                'label'    => 'Fakta (højst 6)',
                'max_rows' => 6,
                'fields'   => [
                    'label' => [
                        'type'        => 'text',
                        'label'       => 'Lille overskrift',
                        'placeholder' => 'Fx Klubnummer',
                        'default'     => '',
                        'max'         => 40,
                    ],
                    'value' => [
                        'type'        => 'text',
                        'label'       => 'Indhold ({klub} = klubbens navn)',
                        'placeholder' => 'Fx 0000 / 0',
                        'default'     => '',
                        'max'         => 80,
                    ],
                ],
                'default' => [
                    ['label' => 'Hovedklub', 'value' => '{klub}'],
                    ['label' => 'Klubnummer', 'value' => '0000 / 0'],
                    ['label' => 'Aktivitet', 'value' => 'Turneringsbridge'],
                    ['label' => 'Spillested', 'value' => 'Klubhuset'],
                ],
            ],
            'signature' => [
                'type'    => 'text',
                'label'   => 'Lille underskrift nederst ({klub} = klubbens navn, tom = ingen)',
                'default' => '{klub}',
                'max'     => 60,
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'heading_style' => [
                'type'    => 'select',
                'label'   => 'Overskriftens udseende',
                'default' => 'Stor tekst',
                'options' => Standard2Kit::HEADING_STYLES,
                'group'   => 'Overskrift',
            ],
            'plain_color' => [
                'type'    => 'color',
                'label'   => 'Stor tekst: farve',
                'default' => '#454545',
                'group'   => 'Overskrift',
            ],
            'heading_bg' => [
                'type'    => 'color',
                'label'   => 'Boks: streg og baggrund',
                'default' => '#5b5959',
                'group'   => 'Overskrift',
            ],
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Boks: tekst',
                'default' => '#ffffff',
                'group'   => 'Overskrift',
            ],
            'heading_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 64,
                'min'     => 28,
                'max'     => 110,
                'unit'    => 'px',
                'group'   => 'Overskrift',
            ],
            'box_color' => [
                'type'    => 'color',
                'label'   => 'Boks',
                'default' => '#ffffff',
                'group'   => 'Fakta',
            ],
            'label_color' => [
                'type'    => 'color',
                'label'   => 'Små overskrifter',
                'default' => '#b1b1b1',
                'group'   => 'Fakta',
            ],
            'value_color' => [
                'type'    => 'color',
                'label'   => 'Indhold',
                'default' => '#484848',
                'group'   => 'Fakta',
            ],
            'signature_color' => [
                'type'    => 'color',
                'label'   => 'Underskrift',
                'default' => '#454545',
                'group'   => 'Fakta',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Fakta',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing = $context->isInlineEditing();
        $facts   = [];

        foreach (array_values((array) ($settings['facts'] ?? [])) as $index => $fact) {
            if (!is_array($fact) || count($facts) >= 6) {
                continue;
            }

            $label = trim((string) ($fact['label'] ?? ''));
            $value = (string) ($fact['value'] ?? '');

            if ($label === '' && trim($value) === '' && !$editing) {
                continue;
            }

            $facts[] = [
                'index'    => $index,
                'label'    => $label,
                'value'    => SiteInfo::expand($value),
                'valueRaw' => $value,
            ];
        }

        $signature = (string) ($settings['signature'] ?? '');

        return static::renderTemplate([
            'title'        => (string) ($settings['title'] ?? ''),
            'headingStyle' => (string) ($styles['heading_style'] ?? 'Stor tekst'),
            'facts'        => $facts,
            'signature'    => trim(SiteInfo::expand($signature)),
            'signatureRaw' => $signature,
            'cssVars'      => static::cssVariables($styles),
            'context'      => $context,
        ]);
    }
}
