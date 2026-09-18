<?php
declare(strict_types=1);

/**
 * Fælles grundlag for alle blokke.
 *
 * Håndterer det, enhver blok ellers ville skulle gentage: standardværdier,
 * indlæsning af template.php, og oversættelsen fra stylingværdier til
 * CSS-variabler.
 *
 * En konkret blok arver herfra og behøver kun beskrive sine egne felter.
 */
abstract class AbstractBlock implements BlockInterface
{
    public static function getStyleSchema(): array
    {
        return [];
    }

    /**
     * Standardværdier for en ny blok, udledt af skemaet.
     *
     * Retter en fejl i den gamle add-block.php, som satte alle felter til
     * tom streng. Resultatet var, at en netop tilføjet blok var usynlig i
     * editoren, og brugeren ikke kunne se hvad der skulle udfyldes.
     *
     * @return array<string, mixed>
     */
    public static function defaultSettings(): array
    {
        return self::defaultsFrom(static::getSchema());
    }

    /** @return array<string, mixed> */
    public static function defaultStyles(): array
    {
        return self::defaultsFrom(static::getStyleSchema());
    }

    /**
     * @param array<string, array<string, mixed>> $schema
     * @return array<string, mixed>
     */
    private static function defaultsFrom(array $schema): array
    {
        $defaults = [];

        foreach ($schema as $name => $field) {
            $defaults[$name] = $field['default'] ?? '';
        }

        return $defaults;
    }

    /**
     * Renderer blokkens template.php.
     *
     * Templaten ligger altid ved siden af blok-klassen, så en blok er én
     * selvstændig mappe: klasse, template og CSS samlet.
     *
     * @param array<string, mixed> $variables Bliver til variabler i templaten.
     */
    protected static function renderTemplate(array $variables): string
    {
        $directory = dirname((new ReflectionClass(static::class))->getFileName());
        $template  = $directory . '/template.php';

        if (!is_file($template)) {
            // Én manglende template må ikke vælte hele siden.
            error_log('Manglende template: ' . $template);
            return '';
        }

        // extract() gør $variables['title'] tilgængelig som $title i
        // templaten. EXTR_SKIP forhindrer, at et feltnavn kan overskrive
        // $template eller $directory og dermed pege på en anden fil.
        extract($variables, EXTR_SKIP);

        ob_start();
        include $template;

        return (string) ob_get_clean();
    }

    /**
     * Oversætter stylingværdier til CSS-variabler på blokkens wrapper.
     *
     * Resultat: style="--title-size:48px;--title-color:#c1121f"
     *
     * Blokkens CSS bruger derefter var(--title-size). Brugerens valg bliver
     * altså aldrig til vilkårlig CSS, kun til værdier i variabler, vi selv
     * har defineret. Kombineret med FieldValidator — der garanterer, at et
     * tal er et tal og en farve er hex — er der ingen vej til CSS-injection.
     *
     * @param array<string, mixed> $styles Validerede stylingværdier.
     */
    /**
     * Fælles størrelsesfelter til en boks i en blok: bredde, højde og
     * afrundede hjørner.
     *
     * En blok kan have flere bokse. Hver boks får sit eget præfiks og
     * dermed sine egne felter og CSS-variabler:
     *
     *   ...static::boxStyleFields('box',  'Blå boks', ['width', 'height', 'radius']),
     *   ...static::boxStyleFields('card', 'Lys boks', ['width', 'radius']),
     *
     * giver felterne box_width, card_width osv. og CSS-variablerne
     * --box-width, --card-width osv. Blokkens egen block.css bestemmer,
     * hvilket element hver variabel bruges på.
     *
     * I editoren samles felterne under boksens navn (se 'group'), og
     * hvert felt vises som en skyder. Kun de nøgler, der gives med i
     * $parts, bliver til felter — en boks uden synlig kant kan derfor
     * udelade 'radius'.
     *
     * Bredde og højde starter på "Auto" (gemt som 0). Så skrives
     * variablen slet ikke, og CSS'en falder tilbage til sit normale
     * udseende. Derfor ser eksisterende sider ud som før, indtil nogen
     * vælger en størrelse.
     *
     * En blok kan give en anden startværdi end 0 med $defaults, fx en
     * afrunding, der hører til designet:
     *
     *   ...static::boxStyleFields('card', 'Kort', ['width', 'radius'], ['radius' => 9]),
     *
     * @param string             $prefix   Bruges i feltnavn og CSS-variabel, fx 'card'.
     * @param string             $group    Boksens navn, som brugeren ser det i editoren.
     * @param array<int, string> $parts    Hvilke felter: 'width', 'height', 'radius'.
     * @param array<string, int> $defaults Startværdier pr. felt. Udeladte starter på 0.
     * @return array<string, array<string, mixed>>
     */
    protected static function boxStyleFields(
        string $prefix,
        string $group,
        array $parts,
        array $defaults = []
    ): array
    {
        $fields = [
            // 'start' er den værdi, skyderen står på, når man slår Auto
            // fra. Den gemmes ikke, før brugeren faktisk slår Auto fra.
            'width' => [
                'label' => 'Bredde',
                'auto'  => true,
                'min'   => 100,
                'max'   => 2000,
                'step'  => 10,
                'start' => 800,
            ],
            'height' => [
                'label' => 'Højde',
                'auto'  => true,
                'min'   => 50,
                'max'   => 1500,
                'step'  => 10,
                'start' => 400,
            ],
            'radius' => [
                'label' => 'Hjørner',
                'auto'  => false,
                'min'   => 0,
                'max'   => 100,
                'step'  => 1,
                'start' => 0,
            ],
        ];

        $result = [];

        foreach ($parts as $part) {
            if (!isset($fields[$part])) {
                continue;
            }

            $result[$prefix . '_' . $part] = $fields[$part] + [
                'type'    => 'size',
                'group'   => $group,
                'unit'    => 'px',
                'default' => (int) ($defaults[$part] ?? 0),
            ];
        }

        return $result;
    }

    protected static function cssVariables(array $styles): string
    {
        $schema      = static::getStyleSchema();
        $declarations = [];

        foreach ($styles as $name => $value) {
            // Kun felter, skemaet kender. Ukendte nøgler ignoreres.
            if (!isset($schema[$name]) || $value === '' || $value === null) {
                continue;
            }

            // "Auto" gemmes som 0. Variablen udelades, så CSS'ens egen
            // standard bruges i stedet for en boks på 0 pixel.
            if (!empty($schema[$name]['auto']) && (int) $value === 0) {
                continue;
            }

            $variable = '--' . str_replace('_', '-', $name);
            $unit     = $schema[$name]['unit'] ?? '';

            $declarations[] = $variable . ':' . $value . $unit;
        }

        return implode(';', $declarations);
    }
}