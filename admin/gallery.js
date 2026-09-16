/* =====================================================================
   Galleriredigering
   Samme princip som editoren: tilstanden holdes i browseren og sendes
   samlet ved Gem. Logikken er skrevet for sig, fordi editor.js er bundet
   til laerredet og sidens blokke og ville fejle her.

   Forskellen paa editoren er uploaden: her kan mange filer vaelges paa en
   gang, og hver fil bliver til sin egen raekke.
   ===================================================================== */

(function () {
    'use strict';

    const form       = document.getElementById('gallery-form');
    const canvas     = document.querySelector('.ed-canvas');
    const saveButton = document.getElementById('save-btn');
    const statusText = document.getElementById('save-status');
    const dropZone   = document.getElementById('gallery-drop');
    const filePicker = document.getElementById('gallery-files');
    const uploadText = document.getElementById('gallery-upload-status');

    if (!form || !canvas) {
        return;
    }

    const repeater    = canvas.querySelector('[data-repeater]');
    const rowList     = repeater.querySelector('.ed-repeater__rows');
    const rowTemplate = repeater.querySelector('[data-row-template]');
    const basePath    = document.body.dataset.basePath;

    // PHP's max_file_uploads staar som standard til 20. Filerne sendes
    // derfor i hold, saa et stort galleri ikke rammer loftet og taber de
    // sidste filer lydloest.
    const BATCH_SIZE = 10;

    let isDirty = false;

    function markDirty() {
        if (isDirty) {
            return;
        }
        isDirty = true;
        saveButton.disabled = false;
        statusText.textContent = 'Ikke gemt';
    }

    document.addEventListener('input', function (event) {
        if (event.target.closest('.ed-panel, .ed-settings')) {
            markDirty();
        }
    });

    window.addEventListener('beforeunload', function (event) {
        if (isDirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    /* --- Raekker ----------------------------------------------------- */

    canvas.addEventListener('click', function (event) {
        const button = event.target.closest('[data-action]');

        if (!button) {
            return;
        }

        if (button.dataset.action === 'add-row') {
            rowList.appendChild(rowTemplate.content.cloneNode(true));
            markDirty();
        }

        if (button.dataset.action === 'remove-row') {
            button.closest('.ed-row').remove();
            markDirty();
        }
    });

    /**
     * Tilfoejer en faerdig raekke for et uploadet billede.
     *
     * Raekken klones fra den skabelon, PHP allerede har tegnet ud fra
     * skemaet — saa felterne her er de samme som alle andre steder.
     */
    function addRow(path, filename) {
        rowList.appendChild(rowTemplate.content.cloneNode(true));

        const row     = rowList.lastElementChild;
        const wrapper = row.querySelector('.ed-image');

        wrapper.querySelector('.ed-image__path').value = path;

        const preview = wrapper.querySelector('.ed-image__preview');
        preview.innerHTML = '';

        const image = document.createElement('img');
        image.src = basePath + '/' + path;
        image.alt = '';
        preview.appendChild(image);

        // Filnavnet som foerste bud paa en beskrivelse. Bedre end tomt,
        // og brugeren kan rette det med det samme.
        const alt = row.querySelector('[data-rfield="alt"]');

        if (alt && filename) {
            alt.value = filename.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ');
        }
    }

    /* --- Flerupload --------------------------------------------------- */

    async function uploadBatch(files) {
        const data = new FormData();

        files.forEach(function (file) {
            data.append('images[]', file);
        });

        const response = await fetch('upload-image.php', { method: 'POST', body: data });
        const raw = await response.text();

        let result;

        try {
            result = JSON.parse(raw);
        } catch (parseError) {
            console.error('Serveren svarede ikke med JSON:', raw);
            throw new Error('Serveren svarede uventet. Se konsollen (F12).');
        }

        if (!response.ok || !result.ok) {
            throw new Error(result.error || 'Ukendt fejl');
        }

        return result;
    }

    async function uploadFiles(fileList) {
        const files = Array.from(fileList);

        if (files.length === 0) {
            return;
        }

        dropZone.classList.add('is-busy');

        let done = 0;
        const problems = [];

        try {
            for (let i = 0; i < files.length; i += BATCH_SIZE) {
                const batch = files.slice(i, i + BATCH_SIZE);

                uploadText.textContent =
                    'Sender ' + (done + 1) + '–' + (done + batch.length) +
                    ' af ' + files.length + ' …';

                const result = await uploadBatch(batch);

                (result.files || []).forEach(function (file) {
                    addRow(file.path, file.name);
                });

                (result.errors || []).forEach(function (message) {
                    problems.push(message);
                });

                done += batch.length;
            }

            // Afviste filer skal naevnes. Ellers tror brugeren, at alle
            // tyve billeder kom med, fordi de nitten gjorde.
            uploadText.textContent = problems.length === 0
                ? files.length + ' billeder tilfoejet — husk at gemme'
                : problems.length + ' fil(er) blev afvist: ' + problems[0];

            markDirty();

        } catch (error) {
            uploadText.textContent = 'Upload fejlede: ' + error.message;
        } finally {
            dropZone.classList.remove('is-busy');
            filePicker.value = '';
        }
    }

    filePicker.addEventListener('change', function () {
        uploadFiles(filePicker.files);
    });

    // Traek-og-slip. dragover skal afvises aktivt, ellers aabner browseren
    // bare billedet i fanen i stedet for at give os filen.
    ['dragenter', 'dragover'].forEach(function (name) {
        dropZone.addEventListener(name, function (event) {
            event.preventDefault();
            dropZone.classList.add('is-over');
        });
    });

    ['dragleave', 'drop'].forEach(function (name) {
        dropZone.addEventListener(name, function () {
            dropZone.classList.remove('is-over');
        });
    });

    dropZone.addEventListener('drop', function (event) {
        event.preventDefault();

        if (event.dataTransfer && event.dataTransfer.files.length) {
            uploadFiles(event.dataTransfer.files);
        }
    });

    /* --- Udskift et enkelt billede ------------------------------------ */

    canvas.addEventListener('change', async function (event) {
        const fileInput = event.target.closest('.ed-image__file');

        if (!fileInput || !fileInput.files.length) {
            return;
        }

        const wrapper    = fileInput.closest('.ed-image');
        const buttonText = wrapper.querySelector('.ed-image__btn-text');

        fileInput.disabled = true;
        buttonText.textContent = 'Sender …';

        try {
            const result = await uploadBatch([fileInput.files[0]]);
            const file   = (result.files || [])[0];

            if (!file) {
                throw new Error(result.errors ? result.errors[0] : 'Filen blev afvist.');
            }

            wrapper.querySelector('.ed-image__path').value = file.path;

            const preview = wrapper.querySelector('.ed-image__preview');
            preview.innerHTML = '';

            const image = document.createElement('img');
            image.src = basePath + '/' + file.path;
            image.alt = '';
            preview.appendChild(image);

            markDirty();

        } catch (error) {
            uploadText.textContent = 'Upload fejlede: ' + error.message;
        } finally {
            fileInput.disabled = false;
            buttonText.textContent = 'Vaelg fil';
            fileInput.value = '';
        }
    });

    /* --- Gem ---------------------------------------------------------- */

    saveButton.addEventListener('click', async function () {
        saveButton.disabled = true;
        statusText.textContent = 'Gemmer …';

        // Raekkefoelgen i DOM'en ER raekkefoelgen i galleriet.
        const images = Array.from(rowList.querySelectorAll('.ed-row')).map(function (row) {
            const values = {};

            row.querySelectorAll('[data-rfield]').forEach(function (input) {
                values[input.dataset.rfield] = input.value;
            });

            return values;
        });

        try {
            const response = await fetch('save-gallery.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: form.dataset.galleryId,
                    name: form.querySelector('[data-gallery-field="name"]').value,
                    images: images
                })
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                throw new Error(result.error || 'Ukendt fejl');
            }

            isDirty = false;
            statusText.textContent = 'Gemt — ' + result.images + ' billeder';
            document.querySelector('.ed-title').textContent = result.name;

        } catch (error) {
            statusText.textContent = 'Kunne ikke gemme: ' + error.message;
            saveButton.disabled = false;
        }
    });
}());