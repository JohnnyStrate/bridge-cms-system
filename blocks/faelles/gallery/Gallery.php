<?php
declare(strict_types=1);

/**
 * Gallery: et gitter af billeder.
 *
 * Blokken er det første sted, hvor et repeater-felt indeholder billeder.
 * Det kræver ingen ny kode nogen steder — editoren tegner allerede
 * billedfelter i rækker, og SiteExporter går skemaet igennem og finder
 * billeder både i almindelige felter og i repeatere.
 *
 * Det er dét, den skemadrevne tilgang skulle give: en ny blok med en ny
 * kombination af felter virker overalt uden ændringer i core.
 */
final class GalleryBlock extends AbstractBlock
{
    public static function type(): string
    {
        return 'gallery';
    }

    public static function label(): string
    {
        return 'Galleri';
    }

     public static function getSchema(): array
    {
        return [
            'title' => [
                'type'    => 'text',
                'label'   => 'Overskrift',
                'default' => '',
            ],
            // Blokken gemmer kun en HENVISNING. Billederne bor i tabellen
            // `galleries`, så det samme galleri kan bruges på flere sider
            // og kun vedligeholdes ét sted.
            'gallery_id' => [
                'type'    => 'gallery',
                'label'   => 'Galleri',
                'default' => 0,
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'columns' => [
                'type'    => 'number',
                'label'   => 'Antal kolonner',
                'default' => 3,
                'min'     => 1,
                'max'     => 6,
            ],
            'gap' => [
                'type'    => 'number',
                'label'   => 'Afstand mellem billeder',
                'default' => 16,
                'min'     => 0,
                'max'     => 64,
                'unit'    => 'px',
            ],
            'radius' => [
                'type'    => 'number',
                'label'   => 'Afrundede hjørner',
                'default' => 4,
                'min'     => 0,
                'max'     => 64,
                'unit'    => 'px',
            ],
            'background_color' => [
                'type'    => 'color',
                'label'   => 'Baggrundsfarve',
                'default' => '#ffffff',
            ],
        ];
    }

   public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $images = [];

        // Opslaget sker i RenderContext, ikke i databasen. Blokken ved
        // hverken, hvor gallerierne kommer fra, eller om vi renderer til
        // editoren eller til en eksporteret fil.
        foreach ($context->galleryImages((int) ($settings['gallery_id'] ?? 0)) as $image) {
            if (!is_array($image)) {
                continue;
            }

            $source = trim((string) ($image['src'] ?? ''));

            // En række uden billedfil ville blive til et tomt hul i
            // gitteret. Den springes over frem for at blive tegnet.
            if ($source === '') {
                continue;
            }

            $images[] = [
                'src'     => $context->asset($source),
                'alt'     => (string) ($image['alt'] ?? ''),
                'caption' => (string) ($image['caption'] ?? ''),
            ];
        }

        return static::renderTemplate([
            'title'   => (string) ($settings['title'] ?? ''),
            'images'  => $images,
            'cssVars' => static::cssVariables($styles),
        ]);
    }

    //     return static::renderTemplate([
    //         'title'   => (string) ($settings['title'] ?? ''),
    //         'images'  => $images,
    //         'cssVars' => static::cssVariables($styles),
    //     ]);
    // }
}