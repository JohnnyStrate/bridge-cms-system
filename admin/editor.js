/* =====================================================================
   Editoren
   Holder sidens tilstand i browseren og sender den samlet, naar brugeren
   trykker Gem. Ingen sideindlaesning undervejs.

   GLOBALE BLOKKE
   En blok med data-global-slot hoerer ikke til siden, men til hele
   sitet. Den samles ind for sig og gemmes i global_blocks, saa en
   aendring slaar igennem paa alle sider paa en gang.

   Al validering sker paa serveren. Det her lag handler om brugerflade,
   ikke om sikkerhed — det kan aendres af enhver med en browserkonsol.
   ===================================================================== */

(function () {
    'use strict';

    const body       = document.body;
    const pageId     = body.dataset.pageId;
    const canvas     = document.getElementById('canvas');
    const saveButton = document.getElementById('save-btn');
    const statusText = document.getElementById('save-status');

    let isDirty = false;

    /* --- Aendret-tilstand ------------------------------------------- */

    function markDirty() {
        if (isDirty) {
            return;
        }
        isDirty = true;
        saveButton.disabled = false;
        saveButton.classList.add('is-dirty');
        statusText.textContent = 'Ikke gemt';
    }

    function markClean(message) {
        isDirty = false;
        saveButton.disabled = true;
        saveButton.classList.remove('is-dirty');
        statusText.textContent = message || '';
    }

    // Fanger baade tastning i tekstfelter og valg i dropdowns og
    // farvevaelgere, uanset hvornaar elementet kom ind i DOM'en.
    document.addEventListener('input', function (event) {
        if (event.target.closest('.ed-panel, .ed-settings')) {
            markDirty();
        }
    });

    // Sidste vaern mod at lukke fanen med ugemt arbejde.
    window.addEventListener('beforeunload', function (event) {
        if (isDirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    /* --- Indsamling af tilstand ------------------------------------- */

    function collectFields(block) {
        const data = { settings: {}, styles: {} };

        // Almindelige felter. Repeater-raekker bruger data-rfield og
        // fanges derfor ikke her.
        block.querySelectorAll('[data-field]').forEach(function (input) {
            const scope = input.dataset.scope;
            if (data[scope]) {
                data[scope][input.dataset.field] = input.value;
            }
        });

        // Repeater-felter bliver til en liste af objekter. Raekkefoelgen
        // i DOM'en er raekkefoelgen i listen.
        block.querySelectorAll('[data-repeater]').forEach(function (repeater) {
            data.settings[repeater.dataset.repeater] = Array.from(
                repeater.querySelectorAll('.ed-row')
            ).map(function (row) {
                const values = {};
                row.querySelectorAll('[data-rfield]').forEach(function (input) {
                    values[input.dataset.rfield] = input.value;
                });
                return values;
            });
        });

        return data;
    }

    // Sidens egne blokke — de globale er ikke med.
    function pageBlockElements() {
        return Array.from(canvas.querySelectorAll('.ed-block:not([data-global-slot])'));
    }

    function collectState() {
        const page = {};

        document.querySelectorAll('[data-page-field]').forEach(function (input) {
            page[input.dataset.pageField] = input.value;
        });

        // Raekkefoelgen i DOM'en ER raekkefoelgen. Serveren udleder
        // sort_order af listens indeks, saa der findes ikke to versioner
        // af sandheden.
        const blocks = pageBlockElements().map(function (block) {
            const fields = collectFields(block);

            return {
                id: block.dataset.blockId || null,
                type: block.dataset.blockType,
                settings: fields.settings,
                styles: fields.styles
            };
        });

        // Globale blokke sendes i deres eget felt. Serveren bruger kun
        // slot'en — bloktypen bestemmes paa serveren, ikke her.
        const globals = Array.from(
            canvas.querySelectorAll('.ed-block[data-global-slot]')
        ).map(function (block) {
            const fields = collectFields(block);

            return {
                slot: block.dataset.globalSlot,
                settings: fields.settings,
                styles: fields.styles
            };
        });

        return { page: page, blocks: blocks, globals: globals };
    }

    /* --- Tilfoej-menuen --------------------------------------------- */

    const addToggle = document.getElementById('add-toggle');
    const addMenu   = document.getElementById('add-menu');

    // En global blok kan kun tilfoejes én gang. Knappen slaas fra, naar
    // blokken ligger paa laerredet, saa brugeren ikke kan lave to.
    function syncGlobalChoices() {
        addMenu.querySelectorAll('[data-add-global]').forEach(function (choice) {
            const exists = canvas.querySelector(
                '.ed-block[data-global-slot="' + choice.dataset.addGlobal + '"]'
            );

            choice.disabled = Boolean(exists);
            choice.title = exists ? 'Ligger allerede paa alle sider' : '';
        });
    }

    /* --- Handlinger paa blokke -------------------------------------- */

    canvas.addEventListener('click', function (event) {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }

        const block    = button.closest('.ed-block');
        const isGlobal = Boolean(block.dataset.globalSlot);

        switch (button.dataset.action) {
            case 'edit': {
                const panel = block.querySelector('.ed-panel');
                const open = panel.hasAttribute('hidden');
                panel.toggleAttribute('hidden', !open);
                button.setAttribute('aria-expanded', String(open));
                break;
            }

            case 'add-row': {
                const repeater = button.closest('[data-repeater]');
                const template = repeater.querySelector('[data-row-template]');

                repeater
                    .querySelector('.ed-repeater__rows')
                    .appendChild(template.content.cloneNode(true));

                markDirty();
                break;
            }

            case 'remove-row':
                button.closest('.ed-row').remove();
                markDirty();
                break;

            case 'delete': {
                // En global blok forsvinder fra ALLE sider. Det skal
                // staa i spoergsmaalet, ikke opdages bagefter.
                const question = isGlobal
                    ? 'Fjern denne sektion fra ALLE sider paa sitet?'
                    : 'Slet denne sektion?';

                if (confirm(question)) {
                    // Blokken fjernes kun i browseren. Den forsvinder
                    // foerst i databasen, naar der gemmes — og indtil da
                    // kan brugeren fortryde ved at forlade siden.
                    block.remove();
                    syncGlobalChoices();
                    markDirty();
                }
                break;
            }

            case 'up': {
                const previous = block.previousElementSibling;

                // Globale blokke er laast til toppen; en side-blok kan
                // ikke skubbes op over dem.
                if (previous && !previous.dataset.globalSlot) {
                    canvas.insertBefore(block, previous);
                    markDirty();
                }
                break;
            }

            case 'down': {
                const next = block.nextElementSibling;

                if (next && !next.dataset.globalSlot) {
                    canvas.insertBefore(next, block);
                    markDirty();
                }
                break;
            }
        }
    });

    /* --- Tilfoej blok ----------------------------------------------- */

    addToggle.addEventListener('click', function () {
        const open = addMenu.hasAttribute('hidden');
        addMenu.toggleAttribute('hidden', !open);
        addToggle.setAttribute('aria-expanded', String(open));
    });

        addMenu.addEventListener('click', function (event) {
        const choice = event.target.closest('[data-add-type], [data-add-global]');

        if (!choice || choice.disabled) {
            return;
        }

        const isGlobal = Boolean(choice.dataset.addGlobal);

        const template = isGlobal
            ? document.querySelector(
                '[data-global-template-for="' + choice.dataset.addGlobal + '"]'
            )
            : document.querySelector(
                '[data-template-for="' + choice.dataset.addType + '"]'
            );

        if (!template) {
            return;
        }

        // Skabelonen indeholder allerede forhaandsvisning og felter med
        // standardvaerdier, tegnet af PHP ud fra blokkens skema.
        const fragment = template.content.cloneNode(true);

        // 'before' = global header, 'after' = global footer, 'page' =
        // sidens eget indhold imellem de to.
        const position = isGlobal
            ? (choice.dataset.globalPosition || 'before')
            : 'page';

        // Den foerste globale blok med position 'after' — alt sidens
        // eget indhold skal ligge OVER den.
        const tail = canvas.querySelector('.ed-block[data-global-position="after"]');

        let added;

        if (position === 'before') {
            canvas.insertBefore(fragment, canvas.firstElementChild);
            added = canvas.firstElementChild;
        } else if (position === 'page' && tail) {
            canvas.insertBefore(fragment, tail);
            added = tail.previousElementSibling;
        } else {
            canvas.appendChild(fragment);
            added = canvas.lastElementChild;
        }

        addMenu.setAttribute('hidden', '');
        addToggle.setAttribute('aria-expanded', 'false');
        syncGlobalChoices();
        markDirty();

        added.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    syncGlobalChoices();

    /* --- Gem --------------------------------------------------------- */

    saveButton.addEventListener('click', async function () {
        saveButton.disabled = true;
        statusText.textContent = 'Gemmer …';

        try {
            const response = await fetch('save-page.php?page_id=' + pageId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(collectState())
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                throw new Error(result.error || 'Ukendt fejl');
            }

            markClean(result.globals ? 'Gemt — navbaren er opdateret paa alle sider' : 'Gemt');

            // Nye blokke havde tomt id. Serveren sender de tildelte id'er
            // tilbage, saa naeste gemning opdaterer dem i stedet for at
            // oprette dem forfra. Globale blokke er ikke med i listen.
            const blocks = pageBlockElements();
            (result.ids || []).forEach(function (id, index) {
                if (blocks[index]) {
                    blocks[index].dataset.blockId = id;
                }
            });

        } catch (error) {
            statusText.textContent = 'Kunne ikke gemme: ' + error.message;
            saveButton.disabled = false;
        }
    });

    /* --- Billedupload ------------------------------------------------ */

    // Fjern billedet. Stien toemmes, og blokken gemmes uden billede.
    // Selve filen slettes ikke fra uploads/ — den kan sagtens vaere i
    // brug et andet sted paa sitet.
    canvas.addEventListener('click', function (event) {
        const button = event.target.closest('[data-action="clear-image"]');

        if (!button) {
            return;
        }

        const wrapper = button.closest('.ed-image');
        const pathInput = wrapper.querySelector('.ed-image__path');
        const preview = wrapper.querySelector('.ed-image__preview');

        pathInput.value = '';
        preview.innerHTML = '<span class="ed-image__placeholder">Intet billede</span>';
        button.hidden = true;

        markDirty();
    });

    // change bobler, saa én lytter daekker ogsaa de billedfelter, der
    // foerst dukker op, naar brugeren tilfoejer en blok eller en raekke.
    canvas.addEventListener('change', async function (event) {
        const fileInput = event.target.closest('.ed-image__file');

        if (!fileInput || !fileInput.files.length) {
            return;
        }

        const wrapper = fileInput.closest('.ed-image');
        const pathInput = wrapper.querySelector('.ed-image__path');
        const preview = wrapper.querySelector('.ed-image__preview');
        // Teksten sidder i sit eget span. Ville vi skrive direkte i
        // etiketten, ville vi slette fil-inputtet, den indeholder.
        const buttonText = wrapper.querySelector('.ed-image__btn-text');

        const data = new FormData();
        data.append('image', fileInput.files[0]);

        // En <label> kan ikke deaktiveres, men det kan inputtet inde i
        // den. Klassen er der kun for at dæmpe knappen visuelt.
        const previousError = wrapper.querySelector('.ed-image__error');
        if (previousError) {
            previousError.remove();
        }

        fileInput.disabled = true;
        wrapper.classList.add('is-uploading');
        buttonText.textContent = 'Sender …';

        try {
            const response = await fetch('upload-image.php', {
                method: 'POST',
                body: data
            });

            // Svaret laeses foerst som tekst. Gaar noget galt paa serveren,
            // svarer PHP med en fejlside i HTML — og response.json() ville
            // saa kaste en uforstaaelig parse-fejl i stedet for at vise,
            // hvad der faktisk gik galt.
            const raw = await response.text();
            let result;

            try {
                result = JSON.parse(raw);
            } catch (parseError) {
                console.error('Serveren svarede ikke med JSON:', raw);
                throw new Error(
                    'Serveren svarede uventet. Se konsollen (F12) for detaljer.'
                );
            }

            if (!response.ok || !result.ok) {
                throw new Error(result.error || 'Ukendt fejl');
            }

            pathInput.value = result.path;
            preview.innerHTML = '';

            const image = document.createElement('img');
            image.src = document.body.dataset.basePath + '/' + result.path;
            image.alt = '';
            preview.appendChild(image);

            // Er billedet lige blevet fjernet, er fjern-knappen skjult.
            // Nu er der et billede igen, saa den skal frem.
            const clearButton = wrapper.querySelector('[data-action="clear-image"]');

            if (clearButton) {
                clearButton.hidden = false;
            }

            // Filen ligger paa disken nu, men stien staar kun i editoren.
            // Foerst naar siden gemmes, kender databasen den.
            markDirty();

        } catch (error) {
            // Fejlen vises ved feltet frem for i en alert, saa beskeden
            // bliver staaende og kan laeses.
            console.error('Upload fejlede:', error);
            showFieldError(wrapper, error.message);
        } finally {
            fileInput.disabled = false;
            wrapper.classList.remove('is-uploading');
            buttonText.textContent = 'Vaelg fil';

            // Nulstilles, saa den samme fil kan vaelges igen bagefter.
            fileInput.value = '';
        }
    });

    function showFieldError(wrapper, message) {
        let notice = wrapper.querySelector('.ed-image__error');

        if (!notice) {
            notice = document.createElement('span');
            notice.className = 'ed-image__error';
            notice.setAttribute('role', 'alert');
            wrapper.appendChild(notice);
        }

        notice.textContent = message;
    }

    /* --- Forhaandsvis ------------------------------------------------ */

    document.getElementById('preview-btn').addEventListener('click', function () {
        // Tilstanden sendes med som en almindelig POST i et nyt vindue,
        // saa forhaandsvisningen viser det ugemte arbejde. Serveren
        // renderer og gemmer intet.
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'preview.php?page_id=' + pageId;
        form.target = '_blank';

        const field = document.createElement('input');
        field.type  = 'hidden';
        field.name  = 'state';
        field.value = JSON.stringify(collectState());

        form.appendChild(field);
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });
}());

/* =====================================================================
   Stoerrelsesfelter (skyder + tal + Auto).

   FieldRenderer tegner hvert felt som:
     [x] Auto   ───●───   [800] px   + et skjult felt med data-field

   Kun det skjulte felt bliver gemt. Denne del holder skyderen,
   tal-feltet og Auto-knappen i sync med det. Auto gemmes som 0.

   Lytterne sidder paa dokumentet, saa felter i blokke, der tilfoejes
   efter sideindlaesning, ogsaa virker.
   ===================================================================== */

(function () {
    'use strict';

    function parts(element) {
        const box = element.closest('[data-size]');

        return {
            box: box,
            auto: box.querySelector('[data-size-auto]'),
            range: box.querySelector('[data-size-range]'),
            number: box.querySelector('[data-size-number]'),
            stored: box.querySelector('input[type="hidden"][data-field]')
        };
    }

    // Holder et tal inden for feltets graenser, saa en indtastning som
    // 12345 eller "abc" ikke kan ende i det gemte felt. Der rundes kun
    // til hele tal — ikke til skyderens trin — saa 455 forbliver 455.
    function clamp(input, value) {
        const min = Number(input.min);
        const max = Number(input.max);
        let number = Math.round(Number(value));

        if (!Number.isFinite(number)) {
            number = min;
        }

        return Math.min(max, Math.max(min, number));
    }

    // Skyderen traekkes: tal-feltet og den gemte vaerdi foelger med.
    document.addEventListener('input', function (event) {
        if (!event.target.matches('[data-size-range]')) {
            return;
        }

        const p = parts(event.target);
        p.number.value = event.target.value;
        p.stored.value = event.target.value;
    });

    // Der skrives i tal-feltet. Skyderen flytter sig, mens man skriver,
    // men selve tallet rettes foerst til, naar man forlader feltet —
    // ellers ville "8" blive rettet til minimum, foer man naar at skrive
    // "800".
    document.addEventListener('input', function (event) {
        if (!event.target.matches('[data-size-number]')) {
            return;
        }

        const p = parts(event.target);
        const value = clamp(p.range, event.target.value);
        p.range.value = value;
        p.stored.value = value;
    });

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-size-number]')) {
            return;
        }

        const p = parts(event.target);
        const value = clamp(p.range, event.target.value);
        event.target.value = value;
        p.range.value = value;
        p.stored.value = value;
    });

    // Auto slaas til eller fra.
    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-size-auto]')) {
            return;
        }

        const p = parts(event.target);
        const isAuto = event.target.checked;

        p.range.disabled = isAuto;
        p.number.disabled = isAuto;
        p.box.classList.toggle('is-auto', isAuto);

        // Slaas Auto fra, gemmes det tal, skyderen allerede staar paa.
        p.stored.value = isAuto ? '0' : p.range.value;
    });
}());
