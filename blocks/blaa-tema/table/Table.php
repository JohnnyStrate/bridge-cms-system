<?php
declare(strict_types=1);

/**
 * Tabel: en overskrift, et antal kolonner — hver i sin egen boks med en
 * blå top og en lys bund — og en lille note under tabellen.
 *
 * KOLONNERNE ER FRIE
 * Kolonnerne skrives som en liste i ét felt: "Underklub; Spilledag;
 * Tidspunkt". Så kan klubben selv fjerne eller tilføje en kolonne (og
 * dermed en boks) uden at blokken skal laves om. Tre kolonner er blot
 * udgangspunktet.
 *
 * RÆKKERNE SKRIVES SOM TEKST
 * Samme princip som ranglisten: én række pr. linje, cellerne adskilt af
 * semikolon eller tabulator. Så kan en tabel kopieres ind fra Excel
 * eller den gamle hjemmeside i ét hug.
 *
 * LINKS — TRE MÅDER
 *
 * 1. En ekstra adresse sidst i rækken gør FØRSTE kolonne til et link.
 *    Det er den nemmeste, når det er navnet, der skal kunne klikkes:
 *
 *        Klør Syv; Tirsdag; 13:00; https://eksempel.dk
 *
 * 2. En celle, der kun indeholder en webadresse eller en e-mail, bliver
 *    selv klikbar. Adressen vises uden "https://", så den er læselig.
 *
 * 3. En hvilken som helst celle kan få sit eget link med en lodret
 *    streg — til de tilfælde, hvor det ikke er første kolonne:
 *
 *        Klør Syv; Tirsdag; 13:00; Bo Holm | mailto:bo@eksempel.dk
 *
 * Kun http, https, mailto, tel, '#' og adresser inden for sitet ('/...')
 * godtages. Alt andet vises som almindelig tekst, så et indsat
 * javascript:-link aldrig kan blive klikbart.
 */
final class TableBlock extends AbstractBlock
{
    /** Rækker, editoren viser, før resten foldes sammen. */
    private const EDITOR_PREVIEW_ROWS = 12;

    /** Flere kolonner end det kan ikke læses på en skærm. */
    private const MAX_COLUMNS = 8;

    public static function type(): string
    {
        return 'table';
    }

    public static function label(): string
    {
        return 'Tabel';
    }

    public static function getSchema(): array
    {
        return [
            'title' => [
                'type'        => 'text',
                'label'       => 'Overskrift',
                'placeholder' => 'Fx Underklubber og spilletider',
                'default'     => 'Underklubber og spilletider',
            ],
            'columns' => [
                'type'        => 'text',
                'label'       => 'Kolonner (adskilt af semikolon)',
                'placeholder' => 'Fx Underklub; Spilledag; Tidspunkt',
                'default'     => 'Underklub; Spilledag; Tidspunkt',
            ],
            'rows' => [
                'type'        => 'textarea',
                'label'       => 'Rækker — én pr. linje. En adresse sidst i rækken gør første kolonne til et link',
                'placeholder' => 'Klør Syv; Tirsdag; 13:00; https://eksempel.dk',
                'rows'        => 10,
                'max'         => 20000,
                'default'     => "Hjerter Dame (mandag aften); Mandag; 19:00; #\n"
                    . "Klør Syv (tirsdag eftermiddag); Tirsdag; 13:00; #\n"
                    . "Ruder Es (onsdag aften); Onsdag; 19:00; #\n"
                    . "Spar Konge (torsdag formiddag); Torsdag; 09:30; #\n"
                    . "Begynderholdet; Fredag; 10:00; #",
            ],
            'note' => [
                'type'        => 'textarea',
                'label'       => 'Lille tekst under tabellen (tom = ingen)',
                'placeholder' => 'Fx kilde eller hvornår listen sidst blev opdateret',
                'rows'        => 2,
                'default'     => "Kilde: klubbens egne oplysninger\nSidst opdateret september 2026",
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
                'label'   => 'Kolonnetoppenes farve',
                'default' => '#213377',
            ],
            'header_color' => [
                'type'    => 'color',
                'label'   => 'Kolonnetoppenes tekstfarve',
                'default' => '#ffffff',
            ],
            'cell_bg' => [
                'type'    => 'color',
                'label'   => 'Boksenes baggrund',
                'default' => '#f8f8f8',
            ],
            'text_color' => [
                'type'    => 'color',
                'label'   => 'Tekstfarve',
                'default' => '#3c3737',
            ],
            'link_color' => [
                'type'    => 'color',
                'label'   => 'Linkfarve',
                'default' => '#5a8bcc',
            ],
            'note_color' => [
                'type'    => 'color',
                'label'   => 'Den lille teksts farve',
                'default' => '#5c5c5c',
            ],
            'text_size' => [
                'type'    => 'number',
                'label'   => 'Skriftstørrelse i tabellen',
                'default' => 17,
                'min'     => 11,
                'max'     => 28,
                'unit'    => 'px',
            ],
            'column_gap' => [
                'type'    => 'number',
                'label'   => 'Afstand mellem boksene (0 = én samlet boks)',
                'default' => 6,
                'min'     => 0,
                'max'     => 48,
                'unit'    => 'px',
            ],
            'font_family' => [
                'type'    => 'select',
                'label'   => 'Skrifttype',
                'default' => 'Jost',
                'options' => FieldValidator::ALLOWED_FONTS,
            ],

            ...static::boxStyleFields('box', 'Hele tabellen', ['width']),
            ...static::boxStyleFields('header', 'Den blå top', ['radius'], ['radius' => 9]),
            ...static::boxStyleFields('cells', 'Den lyse bund', ['radius'], ['radius' => 9]),
        ];
    }

    public static function render(
        array $settings,
        array $styles,
        RenderContext $context
    ): string {
        $editing = $context->isInlineEditing();
        $columns = self::splitLine((string) ($settings['columns'] ?? ''));
        $columns = array_slice(array_values(array_filter(
            $columns,
            static fn (string $column): bool => $column !== ''
        )), 0, self::MAX_COLUMNS);

        // Uden kolonner er der ingen tabel. Én tom kolonne er bedre end
        // at vælte blokken, og i editoren kan man se, hvad der mangler.
        if ($columns === []) {
            $columns = [''];
        }

        $count = count($columns);
        $rows  = [];

        foreach (preg_split('/\R/u', (string) ($settings['rows'] ?? '')) ?: [] as $line) {
            if (trim($line) === '') {
                continue;
            }

            $cells   = self::splitLine($line);
            $rowLink = '';

            // Én celle mere end der er kolonner, og den sidste er en
            // adresse: så er det rækkens link, og det sættes på første
            // kolonne. Er den ekstra celle IKKE en adresse, er det bare en
            // celle for meget, og den skæres fra som før.
            if (count($cells) === $count + 1 && self::isSafeUrl((string) end($cells))) {
                $rowLink = (string) array_pop($cells);
            }

            // Hver række får præcis lige så mange celler, som der er
            // kolonner: for få fyldes op med tomme, for mange skæres fra.
            $cells = array_pad(array_slice($cells, 0, $count), $count, '');
            $row   = array_map([self::class, 'parseCell'], $cells);

            // Et link skrevet direkte i cellen med | vinder over rækkens.
            if ($rowLink !== '' && $row[0]['href'] === '') {
                $row[0]['href'] = $rowLink;
            }

            $rows[] = $row;
        }

        $hidden = 0;

        if ($editing && count($rows) > self::EDITOR_PREVIEW_ROWS) {
            $hidden = count($rows) - self::EDITOR_PREVIEW_ROWS;
            $rows   = array_slice($rows, 0, self::EDITOR_PREVIEW_ROWS);
        }

        // Antallet af kolonner er et heltal, vi selv har talt op, så det
        // er sikkert at skrive ind i style-attributten ved siden af de
        // validerede CSS-variabler.
        $cssVars = static::cssVariables($styles);
        $cssVars = ($cssVars !== '' ? $cssVars . ';' : '') . '--table-cols:' . $count;

        return static::renderTemplate([
            'title'     => (string) ($settings['title'] ?? ''),
            'titleAttr' => $context->inline('title', 'Overskrift'),
            'columns'   => $columns,
            'rows'      => $rows,
            'hidden'    => $hidden,
            'note'      => trim((string) ($settings['note'] ?? '')),
            'editing'   => $editing,
            'cssVars'   => $cssVars,
        ]);
    }

    /**
     * Deler en linje i celler. Semikolon bruges, hvis det findes, ellers
     * tabulator (fra Excel eller en kopieret tabel), ellers mindst to
     * mellemrum.
     *
     * @return array<int, string>
     */
    private static function splitLine(string $line): array
    {
        $separator = match (true) {
            str_contains($line, ';')  => '/;/u',
            str_contains($line, "\t") => '/\t/u',
            default                   => '/\s{2,}/u',
        };

        return array_map('trim', preg_split($separator, trim($line)) ?: []);
    }

    /**
     * En celle: tekst og eventuelt en adresse.
     *
     * Adressen kommer enten efter en lodret streg ("Tekst | adresse"),
     * eller også ER cellen en webadresse eller en e-mail og bliver selv
     * klikbar.
     *
     * @return array{text: string, href: string}
     */
    private static function parseCell(string $cell): array
    {
        $href = '';

        if (str_contains($cell, '|')) {
            [$text, $address] = array_map('trim', explode('|', $cell, 2));
            $cell = $text;

            if (self::isSafeUrl($address)) {
                $href = $address;
            }
        } elseif (preg_match('#^https?://\S+$#i', $cell) === 1 && self::isSafeUrl($cell)) {
            // En ren webadresse. Den vises uden "https://" og "www." og
            // uden afsluttende skråstreg — resten er støj for læseren.
            $href = $cell;
            $cell = rtrim((string) preg_replace('#^https?://(www\.)?#i', '', $cell), '/');
        } elseif (filter_var($cell, FILTER_VALIDATE_EMAIL) !== false) {
            // En ren e-mail bliver et mailto-link.
            $href = 'mailto:' . $cell;
        }

        return [
            'text' => mb_substr($cell, 0, 300, 'UTF-8'),
            'href' => $href,
        ];
    }

    /**
     * Samme regler som FieldValidators url-felter: kun kendte, harmløse
     * protokoller og adresser inden for sitet.
     */
    private static function isSafeUrl(string $url): bool
    {
        if ($url === '#') {
            return true;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }
}
