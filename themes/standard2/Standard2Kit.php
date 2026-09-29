<?php
declare(strict_types=1);

/**
 * Fælles byggeklodser til Standard Bridge 2's sektioner.
 *
 * Står ét sted, så alle sektioner ser ens ud og opfører sig ens.
 * Stylingen ligger i themes/standard2/theme.css.
 *
 *   heading()  Sektionsoverskriften: en tynd streg og under den en mørk
 *              boks med overskriften i hvidt ("Velkommen").
 *   reveal()   Den rolige indgang: elementer med klassen s2-reveal toner
 *              blødt op, én ad gangen, når de kommer ind på skærmen.
 *
 * Filnavnet slutter ikke på Theme.php, så ThemeRegistry ser den ikke som
 * et tema. Autoloaderen finder den, fordi den ligger i temaets rod.
 */
final class Standard2Kit
{
    private function __construct()
    {
    }

    /**
     * Sektionsoverskriften. Farverne kommer fra sektionen, den står i:
     * --heading-bg (streg og boks) og --heading-color (teksten).
     *
     * @param string $field Feltet i blokkens skema, så overskriften kan
     *                      redigeres direkte i editoren.
     */
    public static function heading(string $title, RenderContext $context, string $field = 'title'): string
    {
        return '<div class="s2-heading s2-reveal">'
            . '<span class="s2-heading__line" aria-hidden="true"></span>'
            . '<h2 class="s2-heading__box"><span' . $context->inline($field, 'Overskrift') . '>'
            . e($title) . '</span></h2>'
            . '</div>';
    }

    /**
     * Script til den rolige indgang. Står lige efter sektionen. Ikke i
     * editoren, og ikke hvis brugeren har slået animationer fra — så
     * står alt bare fremme.
     */
    public static function reveal(RenderContext $context): string
    {
        if ($context->isInlineEditing()) {
            return '';
        }

        return <<<'HTML'
<script>
(function () {
    var section = document.currentScript && document.currentScript.previousElementSibling;
    if (!section || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    var items = section.querySelectorAll('.s2-reveal');
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                entry.target.classList.add('is-visible');
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    items.forEach(function (item) {
        item.classList.add('s2-reveal--armed');
        observer.observe(item);
    });
}());
</script>
HTML;
    }
}
