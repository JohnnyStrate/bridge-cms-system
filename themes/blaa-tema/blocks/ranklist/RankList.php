<?php
declare(strict_types=1);

/**
 * Rangliste: spillere med mesterpoint, delt op i grupper (fx Stormester,
 * Kredsmester), hver i sin egen boks.
 *
 * INDTASTNING
 * Listen kan blive lang — over hundrede spillere. Fik hver spiller sine
 * egne felter i editoren, ville panelet få flere hundrede inputs. I
 * stedet har hver GRUPPE ét tekstfelt med én spiller pr. linje:
 *
 *     Anders Holm; 58210; 6420,5; 2310,40
 *
 * Så har panelet lige så mange rækker, som der er grupper, uanset hvor
 * mange spillere der er. Formatet dækker de måder, klubben sandsynligvis
 * har tallene på: skrevet i hånden med semikolon, eller kopieret fra
 * Excel eller den gamle hjemmeside, hvor kolonnerne er adskilt af
 * tabulator. En linje, der ikke kan læses (fx en kopieret overskrift),
 * springes over; i editoren står der, hvor mange det gjaldt.
 *
 * TOTAL REGNES UD
 * Total = bronze/100 + sølv/10 + guld. Formlen er aflæst af klubbens
 * nuværende liste og passer på alle rækker. Klubben taster derfor kun de
 * tre tal, og en kopieret Total-kolonne ignoreres — så kan en tastefejl
 * i Total aldrig give en forkert placering.
 *
 * Gruppen regnes IKKE ud af Total. Mestertitlen afhænger også af andre
 * krav (fx guldpoint), så en spiller med lavere Total kan stå i en højere
 * gruppe. Grupperne skriver klubben selv.
 */
final class RankListBlock extends AbstractBlock
{
    /** Antal spillere pr. gruppe, editoren viser, før resten foldes sammen. */
    private const EDITOR_PREVIEW_ROWS = 5;

    public static function type(): string
    {
        return 'ranklist';
    }

    public static function label(): string
    {
        return 'Rangliste';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'        => 'text',
                'label'       => 'Overskrift',
                'placeholder' => 'Fx Mesterpointliste 2026',
                'default'     => 'Mesterpointliste 2026',
            ],
            'groups' => [
                'type'     => 'repeater',
                'label'     => 'Grupper (én boks pr. gruppe)',
                'add_label' => 'Tilføj gruppe',
                'max_rows'  => 30,
                'fields'   => [
                    'title' => [
                        'type'        => 'text',
                        'label'       => 'Gruppe',
                        'placeholder' => 'Fx Stormester',
                        'default'     => '',
                    ],
                    'players' => [
                        'type'        => 'textarea',
                        'label'       => 'Spillere',
                        'placeholder' => "Én spiller pr. linje:\nNavn; Bronze; Sølv; Guld",
                        'rows'        => 8,

                        // En stor gruppe på 40 spillere fylder ca. 1.600
                        // tegn. Loftet giver rigelig plads uden at være
                        // ubegrænset.
                        'max'         => 20000,
                        'default'     => '',
                    ],
                ],
                'default' => [
                    [
                        'title'   => '2* Stormester',
                        'players' => 'Anders Holm; 58210; 6420,5; 2310,40',
                    ],
                    [
                        'title'   => 'Stormester',
                        'players' => "Birthe Lund; 55980; 2380,4; 120,50\n"
                            . 'Carsten Berg; 22410; 1290,6; 298,75',
                    ],
                    [
                        'title'   => '1* Forbundsmester',
                        'players' => "Dorthe Kjær; 35120; 1012,3; 28,00\n"
                            . "Erik Madsen; 19870; 982,1; 41,50\n"
                            . 'Frida Holt; 13540; 1050,2; 99,25',
                    ],
                    [
                        'title'   => 'Forbundsmester',
                        'players' => "Gustav Nygaard; 18920; 740,8; 44,00\n"
                            . "Hanne Vestergaard; 12880; 1455,0; 18,00\n"
                            . 'Ib Storm; 11210; 502,6; 65,10',
                    ],
                    [
                        'title'   => 'Kredsmester',
                        'players' => "Jonna Friis; 10120; 35,4; 2,00\n"
                            . "Karl Bech; 9010; 108,2; 1,00\n"
                            . "Lene Due; 8840; 64,9; 0,00\n"
                            . "Mads Krog; 7710; 140,1; 3,50\n"
                            . 'Nina Brandt; 7540; 99,3; 2,20',
                    ],
                ],
            ],
            'sort' => [
                'type'    => 'select',
                'label'   => 'Rækkefølge i hver gruppe',
                'default' => 'Efter total',
                'options' => ['Efter total', 'Som indtastet'],
            ],

            // Kolonnernes overskrifter i den blå bjælke. Kan ændres, fx
            // til "Spiller" i stedet for "Navn".
            'label_name' => [
                'type'    => 'text',
                'group'   => 'Kolonneoverskrifter',
                'label'   => 'Navn',
                'default' => 'Navn',
            ],
            'label_bronze' => [
                'type'    => 'text',
                'group'   => 'Kolonneoverskrifter',
                'label'   => 'Bronze',
                'default' => 'Bronze',
            ],
            'label_silver' => [
                'type'    => 'text',
                'group'   => 'Kolonneoverskrifter',
                'label'   => 'Sølv',
                'default' => 'Sølv',
            ],
            'label_gold' => [
                'type'    => 'text',
                'group'   => 'Kolonneoverskrifter',
                'label'   => 'Guld',
                'default' => 'Guld',
            ],
            'label_total' => [
                'type'    => 'text',
                'group'   => 'Kolonneoverskrifter',
                'label'   => 'Total',
                'default' => 'Total',
            ],
        ];
    }

    public static function getStyleSchema(): array
    {
        return [
            'heading_color' => [
                'type'    => 'color',
                'label'   => 'Overskriftens farve',
                'default' => '#272727',
            ],
            'heading_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse på overskrift',
                'default' => 30,
                'min'     => 14,
                'max'     => 72,
                'unit'    => 'px',
            ],
            'header_bg' => [
                'type'    => 'color',
                'label'   => 'Bjælkens farve',
                'default' => '#213377',
            ],
            'header_color' => [
                'type'    => 'color',
                'label'   => 'Bjælkens tekstfarve',
                'default' => '#ffffff',
            ],
            'subtitle_color' => [
                'type'    => 'color',
                'label'   => 'Gruppetitlernes farve',
                'default' => '#3b3b3b',
            ],
            'row_bg' => [
                'type'    => 'color',
                'label'   => 'Boksenes baggrund',
                'default' => '#f8f8f8',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve i boksene',
                'default' => '#3c3737',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse i listen',
                'default' => 25,
                'min'     => 11,
                'max'     => 28,
                'unit'    => 'px',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],

            ...static::boxStyleFields('box', 'Hele listen', ['width']),
            ...static::boxStyleFields('header', 'Den blå bjælke', ['radius'], ['radius' => 9]),
            ...static::boxStyleFields('rows', 'De lyse bokse', ['radius'], ['radius' => 9]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing     = $context->isInlineEditing();
        $sortByTotal = ($settings['sort'] ?? 'Efter total') !== 'Som indtastet';
        $groups      = [];

        foreach ((array) ($settings['groups'] ?? []) as $group) {
            if (!is_array($group)) {
                continue;
            }

            $title  = trim((string) ($group['title'] ?? ''));
            $parsed = self::parsePlayers((string) ($group['players'] ?? ''));
            $rows   = $parsed['rows'];

            // En gruppe uden spillere er en tom boks. På siden springes
            // den over; i editoren vises den, så man kan se, hvor man er.
            if ($rows === [] && !$editing) {
                continue;
            }

            if ($title === '' && $rows === []) {
                continue;
            }

            if ($sortByTotal) {
                // usort er stabil i PHP 8, så spillere med samme total
                // bevarer den rækkefølge, de blev skrevet i.
                usort($rows, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);
            }

            $hidden = 0;

            // I editoren foldes lange grupper sammen, så man ikke skal
            // scrolle forbi hundrede navne for at nå den næste blok. Den
            // færdige side og eksporten viser altid alle.
            if ($editing && count($rows) > self::EDITOR_PREVIEW_ROWS) {
                $hidden = count($rows) - self::EDITOR_PREVIEW_ROWS;
                $rows   = array_slice($rows, 0, self::EDITOR_PREVIEW_ROWS);
            }

            $groups[] = [
                'title'   => $title,
                'rows'    => array_map([self::class, 'formatRow'], $rows),
                'hidden'  => $hidden,
                'skipped' => $parsed['skipped'],
            ];
        }

        return static::renderTemplate([
            'title'     => (string) ($settings['title'] ?? ''),
            'titleAttr' => $context->inline('title', 'Overskrift'),
            'labels'    => [
                'name'   => (string) ($settings['label_name'] ?? 'Navn'),
                'bronze' => (string) ($settings['label_bronze'] ?? 'Bronze'),
                'silver' => (string) ($settings['label_silver'] ?? 'Sølv'),
                'gold'   => (string) ($settings['label_gold'] ?? 'Guld'),
                'total'  => (string) ($settings['label_total'] ?? 'Total'),
            ],
            'groups'    => $groups,
            'editing'   => $editing,
            'cssVars'   => static::cssVariables($styles),
        ]);
    }

    /**
     * Læser en gruppes tekstfelt: én spiller pr. linje.
     *
     * Kolonnerne må være adskilt af semikolon, tabulator eller mindst to
     * mellemrum. Står der kun enkelte mellemrum (fx indtastet i hånden
     * uden semikolon), læses tallene bagfra, og resten er navnet.
     *
     * @return array{rows: array<int, array{name: string, bronze: float, silver: float, gold: float, total: float}>, skipped: int}
     */
    private static function parsePlayers(string $text): array
    {
        $rows    = [];
        $skipped = 0;

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // Skilletegnet vælges pr. linje. Står der semikolon eller
            // tabulator, bruges KUN det — ellers ville "Navn;  123" blive
            // delt både ved semikolonnet og ved de to mellemrum og give
            // en tom kolonne, der forskyder tallene.
            $separator = match (true) {
                str_contains($line, ';')  => '/;/u',
                str_contains($line, "\t") => '/\t/u',
                default                   => '/\s{2,}/u',
            };

            $cells = array_map('trim', preg_split($separator, $line) ?: []);

            if (count($cells) === 1) {
                $cells = self::splitBySpaces($line);
            }

            $name = $cells[0] ?? '';

            // En kopieret overskriftslinje ("Navn  Bronze  Sølv ...") er
            // ikke en fejl, så den tæller ikke med som oversprunget.
            if (in_array(mb_strtolower($name, 'UTF-8'), ['navn', 'name'], true)) {
                continue;
            }

            $numbers = [];
            $given   = 0;

            // Kun de tre første tal bruges. En fjerde kolonne — fx en
            // kopieret Total — ignoreres, fordi Total regnes ud. En
            // manglende kolonne tæller som 0, så "Navn; 1200" er gyldig.
            for ($i = 1; $i <= 3; $i++) {
                $cell   = $cells[$i] ?? '';
                $number = $cell === '' ? 0.0 : self::parseNumber($cell);

                if ($number === null) {
                    break;
                }

                $given += $cell === '' ? 0 : 1;
                $numbers[] = $number;
            }

            // Mindst ét rigtigt tal. Ellers ville en linje med ren tekst
            // blive til en spiller med 0 point i stedet for en advarsel.
            if ($name === '' || count($numbers) < 3 || $given === 0) {
                $skipped++;
                continue;
            }

            [$bronze, $silver, $gold] = $numbers;

            $rows[] = [
                'name'   => mb_substr($name, 0, 120, 'UTF-8'),
                'bronze' => $bronze,
                'silver' => $silver,
                'gold'   => $gold,
                'total'  => round($bronze / 100 + $silver / 10 + $gold, 2),
            ];
        }

        return ['rows' => $rows, 'skipped' => $skipped];
    }

    /**
     * "Anders Holm 58210 6420,5 2310,40" → navn og tal.
     *
     * Navne indeholder selv mellemrum, så linjen kan ikke bare deles ved
     * hvert mellemrum. I stedet tages tallene bagfra, og det, der er
     * tilbage, er navnet.
     *
     * @return array<int, string>
     */
    private static function splitBySpaces(string $line): array
    {
        $tokens  = preg_split('/\s+/u', $line) ?: [];
        $numbers = [];

        while ($tokens !== [] && count($numbers) < 4
            && self::parseNumber((string) end($tokens)) !== null) {
            array_unshift($numbers, (string) array_pop($tokens));
        }

        return [implode(' ', $tokens), ...$numbers];
    }

    /**
     * Et tal skrevet på dansk eller engelsk. Returnerer null, hvis det
     * ikke er et tal.
     *
     *   "2428,70" → 2428.7     "59.430" med komma andetsteds → tusindtal
     *   "1.234,5" → 1234.5     "abc"    → null
     */
    private static function parseNumber(string $value): ?float
    {
        $value = str_replace([' ', "\u{00A0}"], '', trim($value));

        if (str_contains($value, ',')) {
            // Dansk: punktum er tusindtalsskilletegn, komma er decimal.
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        if (preg_match('/^-?\d+(\.\d+)?$/', $value) !== 1) {
            return null;
        }

        // Negative point findes ikke. Et minus er en tastefejl.
        return max(0.0, (float) $value);
    }

    /**
     * Tallene skrevet, som klubben kender dem: dansk komma, ingen
     * tusindtalsskilletegn, og det antal decimaler, hver kolonne plejer
     * at have.
     *
     * @param array{name: string, bronze: float, silver: float, gold: float, total: float} $row
     * @return array<string, string>
     */
    private static function formatRow(array $row): array
    {
        return [
            'name'   => $row['name'],
            'bronze' => self::formatNumber($row['bronze'], 0),
            'silver' => self::formatNumber($row['silver'], 1),
            'gold'   => self::formatNumber($row['gold'], 2),
            'total'  => self::formatNumber($row['total'], 2),
        ];
    }

    /**
     * Mindst $minDecimals decimaler, flere kun hvis tallet har dem, dog
     * højst to. Bronze står som 58210, men 58210,5 mister ikke sin halve.
     */
    private static function formatNumber(float $value, int $minDecimals): string
    {
        $decimals = $minDecimals;

        while ($decimals < 2 && round($value, $decimals) !== round($value, 2)) {
            $decimals++;
        }

        return number_format($value, $decimals, ',', '');
    }
}
