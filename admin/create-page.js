/* =====================================================================
   Forhåndsvisning af skabeloner i "Opret side".

   Øje-knappen åbner template-preview.php i en iframe inde i en <dialog>.
   Iframen giver skabelonen sit eget dokument, så blokkenes CSS ikke
   påvirker adminpanelet.
   ===================================================================== */

(function () {
    'use strict';

    const dialog = document.getElementById('template-preview');

    // Siden har ingen skabeloner, eller browseren kender ikke <dialog>.
    // Så findes der ingen øjne at klikke på, og vi stopper stille.
    if (!dialog || typeof dialog.showModal !== 'function') {
        return;
    }

    const frame = dialog.querySelector('.preview-dialog__frame');
    const title = dialog.querySelector('.preview-dialog__title');

    /* --- Åbn ---------------------------------------------------------- */

    // Én lytter på dokumentet frem for én pr. knap. Så virker det også,
    // hvis der senere kommer flere skabeloner uden en genindlæsning.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-preview-template]');

        if (!button) {
            return;
        }

        // Knappen ligger nu inde i <label>. Uden preventDefault() ville
        // browseren bagefter sende klikket videre til radioknappen, som
        // er det, en <label> normalt gør — og skabelonen ville blive
        // valgt, samtidig med at boksen åbnede.
        event.preventDefault();

        // Id'et kommer fra vores egen markup, men bliver en del af en
        // URL. encodeURIComponent sikrer, at det aldrig kan blive andet
        // end én parameterværdi.
        const id = button.dataset.previewTemplate;

        title.textContent = 'Forhåndsvisning: ' + (button.dataset.previewName || '');
        frame.src = 'template-preview.php?template_id=' + encodeURIComponent(id);

        dialog.showModal();
    });

    /* --- Luk ---------------------------------------------------------- */

    dialog.querySelector('[data-preview-close]').addEventListener('click', function () {
        dialog.close();
    });

    // Et klik på den dæmpede baggrund rammer selve <dialog>-elementet,
    // mens klik inde i boksen rammer dens børn. Det er sådan, vi kan
    // skelne dem fra hinanden.
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    // 'close' affyres uanset hvordan boksen lukkes — kryds, baggrund
    // eller Escape. Iframen tømmes, så den næste skabelon ikke kortvarigt
    // viser den forrige, mens den indlæses.
    dialog.addEventListener('close', function () {
        frame.src = 'about:blank';
    });
}());