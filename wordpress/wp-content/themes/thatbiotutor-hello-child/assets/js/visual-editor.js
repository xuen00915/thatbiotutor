/* Connect the page preview to the existing WordPress field form. */
(() => {
    window.acf?.addAction('ready', () => {
        const preview = document.querySelector('.tbt-visual-preview');
        const picker = document.querySelector('#tbt-editor-section');
        if (!preview || !picker) {
            return;
        }

        const frame = preview.querySelector('iframe');
        const status = preview.querySelector('[role="status"]');
        const savedSection = document.querySelector('input[name="tbt_visual_section"]');
        const stylesheet = document.querySelector('#tbt-visual-editor-css');
        const pageKey = preview.dataset.page;
        const prefix = 'field_tbt_' + pageKey.replaceAll('-', '_') + '_tab_';
        const choices = new Map([...picker.options].map((option) => [option.value, option.textContent]));
        const aliases = JSON.parse(preview.dataset.aliases);
        let markers = [];
        let fromPreview = false;

        const markSelection = (scroll) => {
            savedSection.value = picker.value;
            for (const { button, section } of markers) {
                const selected = button.dataset.sectionKey === picker.value;
                button.setAttribute('aria-pressed', String(selected));
                section.classList.toggle('tbt-visual-section--selected', selected);
            }
            if (scroll) {
                markers.find(({ button }) => button.dataset.sectionKey === picker.value)
                    ?.section.scrollIntoView({ block: 'start' });
            }
        };

        const selectSection = (key) => {
            if (choices.has(key)) {
                picker.value = key;
                picker.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };
        picker.addEventListener('change', () => markSelection(!fromPreview));
        new MutationObserver(() => markSelection(false)).observe(
            document.querySelector('.tbt-native-tabs'),
            { subtree: true, attributes: true, attributeFilter: ['class'] },
        );

        const addMarker = (section, name) => {
            const key = prefix + name;
            if (!section || !choices.has(key)) {
                return;
            }
            const doc = section.ownerDocument;
            const button = doc.createElement('button');
            button.type = 'button';
            button.className = 'tbt-visual-edit-button';
            button.dataset.sectionKey = key;
            button.textContent = 'Edit ' + choices.get(key);
            if (doc.defaultView.getComputedStyle(section).position === 'static') {
                section.style.position = 'relative';
            }
            section.prepend(button);
            markers.push({ button, section });
        };

        const connectPreview = () => {
            const doc = frame.contentDocument;
            if (!doc?.querySelector('#main-content')) {
                status.textContent = 'The preview could not load. Use the Section menu to edit the page.';
                return;
            }
            if (doc.documentElement.classList.contains('tbt-visual-preview-document')) {
                return;
            }

            markers = [];
            doc.documentElement.classList.add('tbt-visual-preview-document');
            const link = doc.createElement('link');
            link.rel = 'stylesheet';
            link.href = stylesheet.href;
            doc.head.append(link);
            for (const section of doc.querySelectorAll('#main-content [data-section]')) {
                const name = section.dataset.section;
                const year = pageKey === 'testimonials' && /^\d{4}$/.test(name);
                addMarker(section, year ? 'years' : (aliases[name] ?? name));
            }
            if (pageKey === 'home') {
                addMarker(doc.querySelector('[data-google-reviews]'), 'google-reviews');
                addMarker(doc.querySelector('.site-footer'), 'shared');
            } else if (pageKey === 'notes' || pageKey === 'celldivision') {
                addMarker(doc.querySelector('#main-content [data-section="hero"] [data-carousel]'), 'slider');
            }
            doc.addEventListener('click', (event) => {
                const marker = event.target.closest('.tbt-visual-edit-button');
                if (marker) {
                    fromPreview = true;
                    selectSection(marker.dataset.sectionKey);
                    fromPreview = false;
                    picker.focus({ preventScroll: true });
                    if (window.matchMedia('(max-width: 1099px)').matches) {
                        picker.scrollIntoView({ block: 'center' });
                    }
                } else if (event.target.closest('a[href]')) {
                    event.preventDefault();
                }
            }, true);
            status.textContent = 'Select Edit on a section. The preview refreshes after you save.';
            markSelection(true);
        };

        selectSection(new URLSearchParams(location.search).get('tbt_visual_section'));
        markSelection(false);
        frame.addEventListener('load', connectPreview);
        if (frame.contentDocument?.readyState === 'complete') {
            connectPreview();
        }
    }, 60);
})();
