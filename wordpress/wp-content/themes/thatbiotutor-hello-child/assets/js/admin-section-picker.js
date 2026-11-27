/* Keep ACF's section switching and validation behind one labelled control. */
(() => {
    window.acf?.addAction('ready', () => {
        for (const thumbnail of document.querySelectorAll('.tbt-bundled-image')) {
            const uploader = thumbnail.previousElementSibling;
            const empty = uploader?.querySelector(':scope > .hide-if-value');
            if (!empty) {
                continue;
            }

            // ACF replaces the src and alt of every image in this field, including the theme preview.
            const image = thumbnail.querySelector('img');
            const original = new Map(['src', 'alt'].map((name) => [name, image.getAttribute(name)]));
            new MutationObserver(() => {
                for (const [name, value] of original) {
                    if (image.getAttribute(name) !== value) {
                        image.setAttribute(name, value);
                    }
                }
            }).observe(image, { attributes: true, attributeFilter: [...original.keys()] });

            empty.prepend(thumbnail);
            const button = empty.querySelector('[data-name="add"]');
            if (button) {
                button.parentElement.replaceChildren(button);
                button.textContent = 'Replace image';
            }
        }

        const tabs = document.querySelector('.postbox[id^="acf-group_tbt_page_"] > .acf-fields > .acf-tab-wrap');
        const links = [...(tabs?.querySelectorAll('.acf-tab-button') ?? [])];
        if (!links.length || tabs.hidden) {
            return;
        }

        const picker = document.createElement('div');
        picker.className = 'tbt-section-picker';

        if (links.length === 1) {
            const heading = document.createElement('h3');
            heading.textContent = links[0].textContent.trim();
            picker.append(heading);
        } else {
            const label = document.createElement('label');
            const select = document.createElement('select');
            select.id = 'tbt-editor-section';
            label.htmlFor = select.id;
            label.textContent = 'Section';

            for (const link of links) {
                select.add(new Option(link.textContent.trim(), link.dataset.key));
            }

            const syncSection = () => {
                const active = links.find((link) => link.parentElement.classList.contains('active'));
                if (active) {
                    select.value = active.dataset.key;
                }
            };
            select.addEventListener('change', () => links[select.selectedIndex]?.click());
            new MutationObserver(syncSection).observe(tabs, {
                subtree: true,
                attributes: true,
                attributeFilter: ['class'],
            });
            syncSection();
            picker.append(label, select);
        }

        tabs.before(picker);
        tabs.classList.add('tbt-native-tabs');
        tabs.hidden = true;
    }, 30);
})();
