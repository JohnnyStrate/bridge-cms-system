<?php
declare(strict_types=1);

/**
 * Hero til Standard Bridge 2.
 *
 * Mørkt foto over hele skærmen med et gråt overlay. Til venstre — i flugt
 * med navbarens første menupunkt — står:
 *   - en stor overskrift (standard: klubbens navn),
 *   - en tynd streg under den,
 *   - klubbens telefon og adresse (fra Indstillinger),
 *   - to knapper: en lys "Tilmeld" med en lille pil og en "Kontakt"
 *     med kun en streg rundt om.
 *
 * Billedet placeres, så kortene står i højre side og ikke går ind under
 * teksten (se "Billedets placering" i stil-panelet).
 */
final class Standard2HeroBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'standard2-hero';
    }

    public static function label(): string
    {
        return 'Hero — Standard Bridge 2';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift ({klub} = klubbens navn)',
                'default' => '{klub}',
                'max'     => 80,
            ],
            'show_contact' => [
                'type'    => 'select',
                'label'   => 'Telefon og adresse under stregen (fra Indstillinger)',
                'default' => 'Vis',
                'options' => ['Vis', 'Skjul'],
            ],
            'button1_label' => [
                'type'        => 'text',
                'label'       => 'Lys knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Tilmeld',
                'max'         => 30,
            ],
            'button1_page' => [
                'type'    => 'page',
                'label'   => 'Lys knap: side',
                'default' => 0,
            ],
            'button1_url' => [
                'type'        => 'url',
                'label'       => 'Lys knap: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'button2_label' => [
                'type'        => 'text',
                'label'       => 'Streg-knap: tekst',
                'placeholder' => 'Tom = ingen knap',
                'default'     => 'Kontakt',
                'max'         => 30,
            ],
            'button2_page' => [
                'type'    => 'page',
                'label'   => 'Streg-knap: side',
                'default' => 0,
            ],
            'button2_url' => [
                'type'        => 'url',
                'label'       => 'Streg-knap: ekstern adresse',
                'placeholder' => 'Indsæt link',
                'default'     => '',
            ],
            'bg_image' => [
                'type'    => 'image',
                'label'   => 'Baggrundsbillede',
                'default' => 'themes/standard2/assets/standard2hero.jpg',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'overlay_color' => [
                'type'    => 'color',
                'label'   => 'Overlay over billedet',
                'default' => '#474748',
                'group'   => 'Billede',
            ],
            'overlay_opacity' => [
                'type'    => 'number',
                'label'   => 'Overlayets styrke',
                'default' => 39,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Baggrund bag billedet',
                'default' => '#211e1e',
                'group'   => 'Billede',
            ],
            'image_position' => [
                'type'    => 'number',
                'label'   => 'Billedets placering (0 = venstre, 100 = højre)',
                'default' => 100,
                'min'     => 0,
                'max'     => 100,
                'unit'    => '%',
                'group'   => 'Billede',
            ],
            'title_color' => [
                'type'    => 'color',
                'label'   => 'Overskrift og kontaktlinjer',
                'default' => '#d8d8d8',
                'group'   => 'Tekst',
            ],
            'line_color' => [
                'type'    => 'color',
                'label'   => 'Stregen under overskriften',
                'default' => '#d3d2d2',
                'group'   => 'Tekst',
            ],
            'title_size' => [
                'type'    => 'number',
                'label'   => 'Overskriftens størrelse',
                'default' => 96,
                'min'     => 32,
                'max'     => 160,
                'unit'    => 'px',
                'group'   => 'Tekst',
            ],
            'button_color' => [
                'type'    => 'color',
                'label'   => 'Knapper (fyld og streg)',
                'default' => '#f6ecec',
                'group'   => 'Knapper',
            ],
            'button_text' => [
                'type'    => 'color',
                'label'   => 'Tekst på lys knap',
                'default' => '#484848',
                'group'   => 'Knapper',
            ],
            'button2_text' => [
                'type'    => 'color',
                'label'   => 'Tekst på streg-knap',
                'default' => '#ffffff',
                'group'   => 'Knapper',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
                'group'   => 'Tekst',
            ],
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing = $context->isInlineEditing();
        $buttons = [];

        foreach ([1, 2] as $n) {
            $label = trim((string) ($settings["button{$n}_label"] ?? ''));

            // I editoren står en tom knap der stadig, så man kan skrive i den.
            if ($label === '' && !$editing) {
                continue;
            }

            $pageId = (int) ($settings["button{$n}_page"] ?? 0);
            $url    = (string) ($settings["button{$n}_url"] ?? '');

            $buttons[] = [
                'field'   => "button{$n}_label",
                'label'   => $label,
                'href'    => $pageId > 0 ? $context->pageUrl($pageId) : ($url !== '' ? $url : '#'),
                'primary' => $n === 1,
            ];
        }

        // Kontaktlinjerne kommer fra Indstillinger. Adressen kan stå på
        // flere linjer dér — her samles den på én.
        $contact = [];

        if (($settings['show_contact'] ?? 'Vis') === 'Vis') {
            $phone   = trim(SiteInfo::get('phone'));
            $address = trim((string) preg_replace('/\s*\n\s*/', ', ', SiteInfo::get('address')));

            foreach ([$phone, $address] as $line) {
                if ($line !== '') {
                    $contact[] = $line;
                }
            }
        }

        $titleRaw = (string) ($settings['title'] ?? '');
        $image    = (string) ($settings['bg_image'] ?? '');

        return static::renderTemplate([
            'title'    => SiteInfo::expand($titleRaw),
            'titleRaw' => $titleRaw,
            'contact'  => $contact,
            'buttons'  => $buttons,
            'image'    => $image !== '' ? $context->asset($image) : '',
            'cssVars'  => static::cssVariables($styles),
            'context'  => $context,
        ]);
    }
}
