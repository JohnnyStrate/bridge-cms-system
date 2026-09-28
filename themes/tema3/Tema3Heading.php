<?php
declare(strict_types=1);

/**
 * Tema 3's titellinje:   >  TITEL  MINITITEL  <
 *
 * Bruges af flere sektioner ("Info med billede", "Tider og steder",
 * "Linkkort"). Den står her ét sted, så en rettelse gælder dem alle.
 * Stylingen ligger i themes/tema3/theme.css.
 *
 * Farverne kommer fra sektionen, den står i — de samme CSS-variabler:
 *     --arrow-color, --title-start, --title-end, --eyebrow-color, --title-size
 * Derfor kan hver sektion stadig have sine egne farver i editoren.
 *
 * ANIMATIONEN
 * Pilene starter lige foran hinanden (><) midt på linjen og skubbes ud til
 * hver sin side, mens ordene wipes frem fra midten. Scriptet måler, hvor
 * langt hver pil skal flyttes, og gemmer det i --t3h-shift. Ikke i
 * editoren, og ikke hvis brugeren har slået animationer fra.
 */
final class Tema3Heading
{
    private function __construct()
    {
    }

    /**
     * @param string $title   Rå tekst — escapes her.
     * @param string $eyebrow Rå tekst — escapes her. Tom = ingen minititel.
     */
    public static function render(string $title, string $eyebrow, RenderContext $context): string
    {
        $editing = $context->isInlineEditing();

        // Pilen tegnes én gang og spejles til højre side i CSS'en.
        $arrow = '<svg viewBox="0 0 24 32" aria-hidden="true" focusable="false">'
            . '<path d="M5 5 L19 16 L5 27" fill="none" stroke="currentColor" stroke-width="5.5"'
            . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';

        $html = '<div class="t3-heading">'
            . '<span class="t3-heading__arrow t3-heading__arrow--left">' . $arrow . '</span>'
            . '<div class="t3-heading__words">'
            . '<h2 class="t3-heading__title"' . $context->inline('title', 'Overskrift') . '>' . e($title) . '</h2>';

        if ($eyebrow !== '' || $editing) {
            $html .= '<span class="t3-heading__eyebrow"' . $context->inline('eyebrow', 'Minititel') . '>'
                . e($eyebrow) . '</span>';
        }

        $html .= '</div>'
            . '<span class="t3-heading__arrow t3-heading__arrow--right">' . $arrow . '</span>'
            . '</div>';

        return $editing ? $html : $html . self::script();
    }

    /** Står lige efter titellinjen, så den skjules, før siden tegnes. */
    private static function script(): string
    {
        return <<<'HTML'
<script>
(function () {
    var heading = document.currentScript && document.currentScript.previousElementSibling;
    if (!heading || !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    var arrows = heading.querySelectorAll('.t3-heading__arrow');

    // Hvor langt skal hver pil flyttes for at stå lige foran den anden
    // midt på linjen? offsetLeft måles UDEN den flytning, der er lagt på,
    // så det kan regnes ud igen, når skrifttypen er hentet.
    function measure() {
        var middle = heading.clientWidth / 2;
        var gap = arrows[0].offsetWidth * 0.6;
        [[arrows[0], -gap], [arrows[1], gap]].forEach(function (pair) {
            var centre = pair[0].offsetLeft + pair[0].offsetWidth / 2;
            pair[0].style.setProperty('--t3h-shift', (middle + pair[1] - centre) + 'px');
        });
    }

    measure();
    heading.classList.add('t3-heading--armed');

    // Titlens bredde afhænger af skrifttypen — vent på den (højst 2 sek.).
    var fonts = document.fonts && document.fonts.ready
        ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 2000); })])
        : Promise.resolve();

    new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                fonts.then(function () {
                    measure();
                    // Browseren skal se pilenes startplads, før de flytter sig.
                    void heading.getBoundingClientRect();
                    heading.classList.add('is-visible');
                });
            }
        });
    }, { rootMargin: '0px 0px -20% 0px' }).observe(heading);
}());
</script>
HTML;
    }
}
