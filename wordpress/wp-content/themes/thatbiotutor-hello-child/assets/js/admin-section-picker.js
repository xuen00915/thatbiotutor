/* Keep ACF's section switching and validation behind one labelled control. */
(() => {
    window.acf?.addAction('ready', () => {
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
