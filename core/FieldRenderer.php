<?php
declare(strict_types=1);

/**
 * Tegner editorens formularfelter ud fra blokkenes skemaer.
 *
 * Ligger som en klasse frem for som funktioner i editor.php, fordi
 * rendering af et side-felt kræver listen over sider. Den skal sendes ind
 * ét sted, ikke hentes fra en global variabel hver gang et felt tegnes.
 *
 * Skemaet er stadig den eneste sandhed om, hvilke felter der findes.
 * Denne klasse oversætter det til brugerflade — den opfinder ingenting.
 */
final class FieldRenderer
{
    /**
     * @param array<int, string> $pageChoices Side-id => titel.
     * @param string             $basePath    Projektets sti under htdocs,
     *                                        så miniaturebilleder kan vises.
     */
       /**
     * @param array<int, string> $pageChoices    Side-id => titel.
     * @param string             $basePath       Projektets sti under htdocs,
     *                                           så miniaturebilleder kan vises.
     * @param array<int, string> $galleryChoices Galleri-id => navn.
     */
    public function __construct(
        private readonly array $pageChoices = [],
        private readonly string $basePath = '',
        private readonly array $galleryChoices = []
    ) {
    }

    /**
     * Hele redigeringspanelet for én blok.
     *
     * @param class-string<BlockInterface> $class
     * @param array<string, mixed>         $settings
     * @param array<string, mixed>         $styles
     */
    public function panel(string $class, array $settings, array $styles): string
    {
        $html = '<div class="ed-panel" hidden>';

        $html .= '<fieldset class="ed-group"><legend>Indhold</legend>';

        foreach ($class::getSchema() as $name => $field) {
            $value = $settings[$name] ?? '';

            $html .= ($field['type'] ?? '') === 'repeater'
                ? $this->repeater($name, $field, is_array($value) ? $value : [])
                : $this->field('settings', $name, $field, $value);
        }

        $html .= '</fieldset>';

        $styleSchema = $class::getStyleSchema();

        if ($styleSchema !== []) {
            $html .= '<fieldset class="ed-group"><legend>Udseende</legend>';

            // Felter med en 'group' (fx "Blå boks") samles i deres egen
            // lille ramme med gruppens navn som overskrift. Så står navnet
            // der én gang i stedet for foran hvert eneste felt. Felter
            // uden gruppe vises først, som de altid har gjort.
            $groups = [];

            foreach ($styleSchema as $name => $field) {
                $group = (string) ($field['group'] ?? '');

                if ($group === '') {
                    $html .= $this->field('styles', $name, $field, $styles[$name] ?? '');
                } else {
                    $groups[$group][$name] = $field;
                }
            }

            foreach ($groups as $group => $fields) {
                $html .= '<fieldset class="ed-subgroup"><legend>' . e($group) . '</legend>';

                foreach ($fields as $name => $field) {
                    $html .= $this->field('styles', $name, $field, $styles[$name] ?? '');
                }

                $html .= '</fieldset>';
            }

            $html .= '</fieldset>';
        }

        return $html . '</div>';
    }

    /**
     * Ét formularfelt.
     *
     * @param array<string, mixed> $field
     */
    public function field(string $scope, string $name, array $field, mixed $value): string
    {
        // Id'et skal være unikt på tværs af alle blokke på siden, så
        // etiketten peger på det rigtige felt.
        $id    = 'f_' . $scope . '_' . $name . '_' . bin2hex(random_bytes(3));
        $label = (string) ($field['label'] ?? $name);

        $attributes = 'id="' . e($id) . '"'
            . ' data-scope="' . e($scope) . '"'
            . ' data-field="' . e($name) . '"';

        if (($field['type'] ?? '') === 'size') {
            return $this->size($id, $scope, $name, $label, $field, (int) $value);
        }

        return '<p class="ed-field">'
            . '<label for="' . e($id) . '">' . e($label) . '</label>'
            . $this->input($attributes, $field, $value)
            . '</p>';
    }

    /**
     * En størrelse: skyder + tal-felt + evt. en "Auto"-knap.
     *
     *   Bredde  [x] Auto  ───●─────  [800] px
     *
     * Den værdi, der gemmes, ligger i et skjult felt med data-field —
     * præcis som alle andre felter, så editor.js' indsamling og serveren
     * ikke skal kende til skyderen. Skyder, tal-felt og Auto er kun
     * betjening; editor.js holder dem og det skjulte felt i sync.
     *
     * Auto gemmes som 0. Mens Auto er slået til, er skyder og tal-felt
     * slået fra og viser 'start', så der står et fornuftigt tal klar, når
     * man slår Auto fra.
     *
     * @param array<string, mixed> $field
     */
    private function size(string $id, string $scope, string $name, string $label, array $field, int $value): string
    {
        $min     = (int) ($field['min'] ?? 0);
        $max     = (int) ($field['max'] ?? 9999);
        $step    = max(1, (int) ($field['step'] ?? 1));
        $canAuto = !empty($field['auto']);
        $isAuto  = $canAuto && $value <= 0;
        $unit    = (string) ($field['unit'] ?? '');

        // Det tal, betjeningen viser. Under Auto: startværdien.
        $shown = $isAuto ? (int) ($field['start'] ?? $min) : $value;
        $shown = max($min, min($max, $shown));

        $disabled = $isAuto ? ' disabled' : '';
        $bounds   = ' min="' . $min . '" max="' . $max . '" step="' . $step . '"';

        $auto = $canAuto
            ? '<label class="ed-size__auto">'
                . '<input type="checkbox" data-size-auto' . ($isAuto ? ' checked' : '') . '> Auto'
                . '</label>'
            : '';

        return '<div class="ed-field ed-size' . ($isAuto ? ' is-auto' : '') . '" data-size>'
            . '<label class="ed-size__label" for="' . e($id) . '">' . e($label) . '</label>'
            . $auto
            . '<input type="range" class="ed-size__range" id="' . e($id) . '"'
                . ' data-size-range' . $bounds
                . ' value="' . $shown . '"' . $disabled . '>'
            . '<span class="ed-size__value">'
                // Tal-feltet tager hele tal, også mellem skyderens trin,
                // så man kan skrive præcis 455, selvom skyderen hopper i 10.
                . '<input type="number" class="ed-size__number" data-size-number'
                . ' min="' . $min . '" max="' . $max . '" step="1"'
                . ' value="' . $shown . '"' . $disabled
                . ' aria-label="' . e($label . ($unit !== '' ? ' i ' . $unit : '')) . '">'
                . ($unit !== '' ? '<span class="ed-size__unit">' . e($unit) . '</span>' : '')
            . '</span>'
            . '<input type="hidden" data-scope="' . e($scope) . '" data-field="' . e($name) . '"'
                . ' value="' . ($isAuto ? 0 : $shown) . '">'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $field
     */
    private function input(string $attributes, array $field, mixed $value): string
    {
        // Placeholder er valgfri i skemaet og hører kun hjemme på felter,
        // brugeren selv skriver i — ikke på select, color eller number,
        // hvor der altid står en værdi i forvejen.
        $placeholder = isset($field['placeholder'])
            ? ' placeholder="' . e((string) $field['placeholder']) . '"'
            : '';

        return match ($field['type'] ?? 'text') {
            'textarea' => '<textarea ' . $attributes . $placeholder . ' rows="4">'
                . e((string) $value) . '</textarea>',

            'color' => '<input type="color" ' . $attributes
                . ' value="' . e($value !== '' ? (string) $value : '#000000') . '">',

            'number' => '<input type="number" ' . $attributes
                . ' value="' . e((string) $value) . '"'
                . ' min="' . (int) ($field['min'] ?? 0) . '"'
                . ' max="' . (int) ($field['max'] ?? 9999) . '">',

            'select' => $this->select($attributes, $field['options'] ?? [], (string) $value),

            // Sider vælges fra en liste frem for at skrives som adresse.
            // Så kan brugeren ikke stave forkert, og linket overlever, at
            // målsiden får en ny slug.
            'page' => $this->pageSelect($attributes, (int) $value),
            'gallery' => $this->gallerySelect($attributes, (int) $value),
            'image' => $this->imagePicker($attributes, (string) $value),

            default => '<input type="text" ' . $attributes . $placeholder
                . ' value="' . e((string) $value) . '">',
        };
    }

    /**
     * @param array<int, string> $options
     */
    private function select(string $attributes, array $options, string $value): string
    {
        $html = '<select ' . $attributes . '>';

        foreach ($options as $option) {
            $html .= '<option value="' . e($option) . '"'
                . ($value === (string) $option ? ' selected' : '')
                . '>' . e($option) . '</option>';
        }

        return $html . '</select>';
    }

    private function pageSelect(string $attributes, int $selected): string
    {
        $html = '<select ' . $attributes . '>'
            . '<option value="0">— ingen —</option>';

        foreach ($this->pageChoices as $id => $title) {
            $html .= '<option value="' . (int) $id . '"'
                . ($selected === (int) $id ? ' selected' : '')
                . '>' . e($title) . '</option>';
        }

        return $html . '</select>';
    }    /**
     * Et galleri vælges fra listen over dem, brugeren har oprettet.
     *
     * Linket åbner galleriadministrationen i en ny fane, så et manglende
     * galleri kan oprettes uden at forlade en igangværende redigering.
     * Listen opdateres, næste gang editoren indlæses.
     */
    private function gallerySelect(string $attributes, int $selected): string
    {
        $html = '<span class="ed-gallery"><select ' . $attributes . '>'
            . '<option value="0">— vælg galleri —</option>';

        foreach ($this->galleryChoices as $id => $name) {
            $html .= '<option value="' . (int) $id . '"'
                . ($selected === (int) $id ? ' selected' : '')
                . '>' . e($name) . '</option>';
        }

        return $html . '</select>'
            . '<a class="ed-gallery__link" href="galleries.php" target="_blank"'
            . ' rel="noopener">Opret / redigér gallerier</a></span>';
    }

    /**
     * Et billedfelt: miniature, filvælger og den gemte sti.
     *
     * Stien står stadig i et almindeligt tekstfelt — det er dét, gemme-
     * flowet læser, og det gør feltet identisk med alle andre felter set
     * fra JavaScript. Feltet er skrivebeskyttet, fordi værdien nu kommer
     * fra en upload; skal en sti rettes i hånden, kan det gøres i
     * databasen.
     *
     * Selve upload-knappen og den skjulte fil-input håndteres af
     * editor.js. Her tegnes kun markup'en.
     */
    private function imagePicker(string $attributes, string $value): string
    {
        $thumbnail = $value !== ''
            ? $this->basePath . '/' . ltrim($value, '/')
            : '';

        return '<span class="ed-image">'
            . '<span class="ed-image__preview">'
            . ($thumbnail !== ''
                ? '<img src="' . e($thumbnail) . '" alt="">'
                : '<span class="ed-image__placeholder">Intet billede</span>')
            . '</span>'
            . '<input type="text" ' . $attributes
            . ' class="ed-image__path" value="' . e($value) . '" readonly'
            . ' placeholder="Ingen fil valgt">'
            // Fil-inputtet ligger inde i en <label>. Klikker man på
            // etiketten, aktiverer browseren selv den skjulte input.
            //
            // Alternativet — en <button> der kalder .click() på inputtet
            // via JavaScript — er skrøbeligt, fordi browsere er
            // restriktive omkring, hvornår et programmatisk klik må åbne
            // en filvælger. Her er der ingen JavaScript involveret.
            . '<label class="ed-image__btn">'
            . '<span class="ed-image__btn-text">Vælg fil</span>'
            . '<input type="file" class="ed-image__file" accept="image/*" hidden>'
            . '</label>'
            . '</span>';
    }

    /**
     * Et repeater-felt: et vilkårligt antal ens rækker.
     *
     * Bruges til punktlister og navigationslinks. Rækkerne ligger i
     * blokkens egen JSON, ikke som selvstændige blokke i databasen — det
     * er dét, der sparer os for indlejrede blokke med parent_id, og dermed
     * for rekursiv rendering og forældreløse rækker ved sletning.
     *
     * Den tomme <template> nederst bruges, når brugeren tilføjer en række.
     * Så bygger JavaScript ikke felter selv; det kloner det, PHP allerede
     * har tegnet ud fra skemaet.
     *
     * @param array<string, mixed>             $field
     * @param array<int, array<string, mixed>> $rows
     */
    public function repeater(string $name, array $field, array $rows): string
    {
        $html = '<div class="ed-repeater" data-repeater="' . e($name) . '">'
            . '<span class="ed-repeater__label">'
            . e((string) ($field['label'] ?? $name)) . '</span>'
            . '<div class="ed-repeater__rows">';

        foreach ($rows as $row) {
            $html .= $this->row($field['fields'] ?? [], is_array($row) ? $row : []);
        }

        return $html . '</div>'
            . '<button type="button" class="ed-repeater__add" data-action="add-row">'
            . '+ Tilføj række</button>'
            . '<template data-row-template>'
            . $this->row($field['fields'] ?? [], [])
            . '</template>'
            . '</div>';
    }

    /**
     * @param array<string, array<string, mixed>> $subSchema
     * @param array<string, mixed>                $row
     */
    private function row(array $subSchema, array $row): string
    {
        $html = '<div class="ed-row">';

        foreach ($subSchema as $name => $field) {
            $attributes = 'data-rfield="' . e($name) . '"'
                . ' aria-label="' . e((string) ($field['label'] ?? $name)) . '"';

            $html .= $this->input($attributes, $field, $row[$name] ?? '');
        }

        return $html
            . '<button type="button" class="ed-btn ed-btn--delete"'
            . ' data-action="remove-row" aria-label="Fjern række">&times;</button>'
            . '</div>';
    }
}