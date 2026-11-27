// What a client does in wp-admin, end to end: open a protected page, edit words with the formatting buttons,
// change a link, add a class year, and see the public page change. Needs a local WordPress with an admin user
// (wp-env default admin/password); it restores everything it touches. Not part of the static-site CI run.
//   TBT_RUNTIME_URL=http://localhost:8895 node tests/admin-editing-runtime.js
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/$/, '');
const user = process.env.TBT_ADMIN_USER || 'admin';
const pass = process.env.TBT_ADMIN_PASS || 'password';

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1400, height: 1000 } });
    page.setDefaultTimeout(30000);
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    await page.goto(base + '/wp-login.php');
    await page.fill('#user_login', user);
    await page.fill('#user_pass', pass);
    await page.click('#wp-submit');
    await page.waitForLoadState('load');

    // The regular Pages screen is the starting point for editing site pages.
    const sidebar = page.locator('#adminmenu');
    assert.equal(await sidebar.getByRole('link', { name: 'Website Content', exact: true }).count(), 0);
    await sidebar.getByRole('link', { name: 'Pages', exact: true }).click();
    const pageNames = async () => {
      const rows = page.locator('#the-list a.row-title');
      await rows.first().waitFor();
      return rows.allTextContents();
    };
    assert.deepEqual(await pageNames(), [
      'Home', 'Classes', 'Sec 3', 'Sec 4', 'Results & Testimonials', 'Free Bio Notes', 'About Us', 'Contact', 'Privacy Policy',
    ]);
    assert.match(await page.locator('#the-list tr', { hasText: 'Privacy Policy' }).innerText(), /Draft/);
    assert.ok(await sidebar.getByRole('link', { name: 'Main Pages', exact: true }).isVisible());
    const aboutFromPages = await page.locator('#the-list a.row-title', { hasText: 'About Us' }).getAttribute('href');
    const views = page.locator('.subsubsub');
    await views.getByRole('link', { name: 'Additional Pages', exact: true }).click();
    await page.waitForURL('**/edit.php?post_type=page&tbt_page_list=additional');
    const additionalNames = await pageNames();
    for (const title of ['Cell Division Crash Course', 'Our Causes', 'Tuition Recommendations', 'IntensiveFix']) {
      assert.ok(additionalNames.includes(title), `Additional Pages includes ${title}`);
    }
    assert.ok(!additionalNames.includes('Home'));
    await views.getByRole('link', { name: 'All Pages', exact: true }).click();
    await page.waitForURL('**/edit.php?post_type=page&tbt_page_list=all');
    assert.ok((await pageNames()).includes('Home') && (await pageNames()).includes('IntensiveFix'));
    for (const collection of ['Testimonials', 'Class Years', 'Tutors', 'FAQs']) {
      assert.ok(await sidebar.locator('a.menu-top').getByText(collection, { exact: true }).isVisible());
    }

    assert.equal(await sidebar.locator('a[href="edit.php?post_type=tbt_result"]').count(), 0, 'The unused Results menu is hidden.');

    await page.goto(new URL('sec-3-pure-g3-biology-tuition/', base + '/').href);
    assert.ok(await page.locator('main h1').isVisible(), 'A signed-in administrator can still view the public page.');
    assert.equal(await page.locator('.tbt-visual-edit-button').count(), 0, 'Edit buttons are only in the visual preview.');

    // Click Update/Publish and wait for WordPress to finish saving and reload the screen.
    const save = async () => { await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), page.locator('#publish').click()]); };
    const editLink = async (title) => {
      await page.goto(`${base}/wp-admin/edit.php?post_type=page&s=${encodeURIComponent(title)}`);
      const href = await page.locator('a.row-title', { hasText: title }).first().getAttribute('href');
      return href.startsWith('http') ? href : base + href;
    };

    // 1. A protected page opens on a clear edit screen: field tabs first, no empty block canvas.
    const section = page.getByLabel('Section', { exact: true });
    const chooseSection = async (label) => {
      await section.selectOption({ label });
    };

    const aboutEdit = await editLink('About Us');
    assert.equal(new URL(aboutFromPages, base).href, aboutEdit, 'The Pages list opens the real page editor.');
    await page.goto(aboutEdit);
    await section.waitFor({ timeout: 5000 });
    assert.equal(await page.locator('#editor .edit-post-layout, .block-editor-writing-flow').count(), 0, 'Protected pages must not open the empty block editor.');
    assert.ok(await page.locator('.tbt-page-guide').isVisible(), 'A short guide must explain how to edit the page.');
    assert.equal(await page.locator('#postdivrich').count(), 0, 'The unused page-body editor must be hidden.');
    const tabs = await section.locator('option').allTextContents();
    assert.ok(!tabs.includes('SEO'), 'The classic editor has no SEO section.');
    assert.equal(await page.locator('.acf-tab-button:visible').count(), 0, 'The section picker replaces the crowded row of tabs.');
    for (const tab of ['Page headings and copy', 'Top banner', 'Our Mission', 'Quality Teaching', 'Message From The Founder', 'What We Believe In']) assert.ok(tabs.includes(tab), `About needs the "${tab}" tab: ${tabs.join(', ')}`);

    // 2. The formatted-text toolbar is the short, client-safe one, including Highlight.
    await chooseSection('Our Mission');
    const body = page.locator('.acf-field[data-name="about_mission_body"]');
    await body.locator('.mce-toolbar').first().waitFor();
    const buttons = await body.locator('.mce-btn[aria-label]').evaluateAll((els) => els.map((e) => e.getAttribute('aria-label').replace(/\s*\(.*\)$/, '')));
    for (const label of ['Bold', 'Italic', 'Underline', 'Highlight in green', 'Insert/edit link', 'Bulleted list']) assert.ok(buttons.includes(label), `Toolbar must offer "${label}": ${buttons.join(', ')}`);
    for (const label of ['Blockquote', 'Align left', 'Text color', 'Insert Read More tag']) assert.ok(!buttons.includes(label), `Toolbar must not offer "${label}"`);

    // 3. Edit the text with the real buttons, save, and see it on the public page.
    const original = await page.evaluate(() => { const id = document.querySelector('.acf-field[data-name="about_mission_body"] textarea').id; return tinymce.get(id).getContent(); });
    const stamp = 'Client edit ' + Date.now();
    await page.evaluate((text) => { const id = document.querySelector('.acf-field[data-name="about_mission_body"] textarea').id; const ed = tinymce.get(id); ed.setContent('<p>' + text + '</p>'); ed.execCommand('selectAll'); }, stamp);
    await body.locator('.mce-btn[aria-label^="Underline"] button').click();
    await body.locator('.mce-btn[aria-label="Highlight in green"] button').click();
    const edited = await page.evaluate(() => { const id = document.querySelector('.acf-field[data-name="about_mission_body"] textarea').id; return tinymce.get(id).getContent(); });
    assert.ok(/<mark>/.test(edited) && /<u>/.test(edited), `Highlight must write <mark> and Underline <u>: ${edited}`);
    await save();
    const pub = await browser.newPage();
    await pub.goto(base + '/about/');
    const html = await pub.locator('.tbt-about-mission .tbt-reading-width').innerHTML();
    assert.ok(html.includes(stamp) && /<mark>/.test(html) && /<u>/.test(html), `The public About page must show the edited, formatted text: ${html}`);

    // 4. Restore the approved text.
    await page.goto(aboutEdit);
    await section.waitFor({ timeout: 5000 });
    await chooseSection('Our Mission');
    await page.evaluate((html) => { const id = document.querySelector('.acf-field[data-name="about_mission_body"] textarea').id; tinymce.get(id).setContent(html); }, original);
    await save();
    await pub.reload();
    assert.ok(!(await pub.locator('.tbt-about-mission').innerText()).includes(stamp), 'The restored text must replace the test edit.');

    // The inclusions heading uses the same green text in TinyMCE as in the page preview.
    const sec4Edit = await editLink('Sec 4');
    await page.goto(sec4Edit + '&tbt_visual=1');
    const sec4Preview = page.frameLocator('iframe[title="Page preview"]');
    await sec4Preview.getByRole('button', { name: 'Edit What students get', exact: true }).click();
    const inclusions = page.locator('.acf-field[data-name="sec4_inclusions_line2"]');
    await inclusions.locator('.mce-btn[aria-label="Green text"]').waitFor({ timeout: 5000 });
    assert.equal(await inclusions.locator('.mce-btn[aria-label="Highlight in green"]').count(), 0);
    const accent = inclusions.frameLocator('.mce-edit-area iframe').locator('mark');
    const editorAccent = await accent.evaluate(el => ({
      color: getComputedStyle(el).color,
      background: getComputedStyle(el).backgroundColor,
    }));
    const pageAccent = await sec4Preview.locator('[data-section="inclusions"] h2 span').evaluate(el => getComputedStyle(el).color);
    assert.equal(editorAccent.color, pageAccent, 'Green text matches the site’s heading colour.');
    assert.equal(editorAccent.background, 'rgba(0, 0, 0, 0)', 'Green text has no highlight background.');

    // 5. A new class year appears on the page and in the year menu, newest first, then is removed.
    await page.goto(`${base}/wp-admin/post-new.php?post_type=tbt_class_year`);
    await page.fill('#title', 'Class of 2099');
    await page.locator('.acf-field[data-name="tbt_year_value"] input').fill('2099');
    await page.locator('.acf-field[data-name="tbt_year_heading"] input').fill('Class of 2099');
    await save();
    await page.waitForFunction(() => /Published/.test(document.querySelector('#post-status-display')?.textContent || ''));
    await pub.goto(base + '/testimonials/');
    assert.equal(await pub.locator('#class-of-2099').count(), 1, 'A new Class Year must add its section.');
    const order = await pub.locator('.tbt-testimonials-year').evaluateAll((els) => els.map((e) => e.dataset.section));
    assert.equal(order[0], '2099', `The newest year must come first: ${order.join(',')}`);
    assert.equal(await pub.locator('.tbt-year-nav__group').first().locator('.tbt-year-nav__title').innerText(), 'Class of 2099', 'The year menu must list the new year first.');
    await page.goto(`${base}/wp-admin/edit.php?post_type=tbt_class_year`);
    const row = page.locator('tr', { hasText: 'Class of 2099' }).first();
    await row.hover();
    await row.locator('.submitdelete').click();
    await pub.reload();
    assert.equal(await pub.locator('#class-of-2099').count(), 0, 'Deleting the year removes its section.');

    // 6. Home: a plain text field and the red/green title accents reach the public page, then are restored.
    const homeEdit = await editLink('Home');
    await page.goto(homeEdit);
    await section.waitFor({ timeout: 5000 });
    await chooseSection('Top of the page');
    assert.ok(!(await section.locator('option').allTextContents()).includes('Page headings and copy'), 'Home leaves out the empty general section.');
    await page.setViewportSize({ width: 390, height: 844 });
    await chooseSection('Track record');
    assert.ok(await page.locator('.acf-field[data-name="home_track_video_url"]').isVisible(), 'The section selector works on a narrow screen.');
    await page.setViewportSize({ width: 1400, height: 1000 });
    await page.getByRole('link', { name: 'Open visual editor', exact: true }).click();
    await page.waitForURL(/tbt_visual=1/);
    await section.waitFor();
    assert.ok(!(await section.locator('option').allTextContents()).includes('SEO'), 'The visual editor has no SEO section.');
    const preview = page.frameLocator('iframe[title="Page preview"]');
    await preview.getByRole('button', { name: 'Edit Track record', exact: true }).click();
    assert.equal(await section.locator('option:checked').textContent(), 'Track record');
    assert.ok(await page.locator('.acf-field[data-name="home_track_video_url"]').isVisible());
    const [previewBox, panelBox] = await Promise.all([page.locator('iframe[title="Page preview"]').boundingBox(), section.boundingBox()]);
    assert.ok(previewBox.x + previewBox.width < panelBox.x, 'The visual layout and fields sit side by side.');
    await preview.getByRole('button', { name: 'Edit FastTrack system', exact: true }).click();
    const paragraph = page.locator('.acf-field[data-name="home_fasttrack_lead"] textarea');
    assert.match(await paragraph.inputValue({ timeout: 5000 }), /Why should students write their own notes/);
    assert.ok(await paragraph.evaluate(el => el.scrollHeight <= el.clientHeight + 1), 'The full FastTrack paragraph is visible in the side panel.');
    await preview.getByRole('button', { name: 'Edit Top of the page', exact: true }).click();
    for (const name of ['home_hero_refund', 'home_whatsapp_message']) {
      const wrapping = page.locator(`.acf-field[data-name="${name}"] textarea`);
      assert.ok(await wrapping.evaluate(el => el.scrollHeight <= el.clientHeight + 1), `The full ${name} is visible.`);
    }
    for (let n = 1; n <= 5; n++) {
      const name = 'home_hero_image_' + String(n).padStart(2, '0');
      const field = page.locator(`.acf-field[data-name="${name}"]`);
      if (!(await field.locator('.acf-image-uploader.has-value').count())) {
        const thumbnail = field.locator('.tbt-bundled-image img');
        await thumbnail.waitFor({ timeout: 5000 });
        const displayedImage = preview.locator('img[data-media-key="home_hero_' + String(n).padStart(2, '0') + '"]');
        assert.equal(await thumbnail.getAttribute('src'), await displayedImage.getAttribute('src'), 'The thumbnail matches the picture on the page.');
        assert.equal(await field.locator('.acf-image-uploader input[type="hidden"]').inputValue(), '', 'Showing the default does not set an attachment.');
        assert.ok(await field.getByRole('link', { name: 'Replace image', exact: true }).isVisible());
      }
    }
    const kicker = page.locator('.acf-field[data-name="home_specialist_line"] input');
    const kickerOriginal = await kicker.inputValue();
    const kickerStamp = 'Client kicker ' + Date.now();
    await kicker.fill(kickerStamp);
    await save();
    await page.waitForURL(/tbt_visual=1/);
    await preview.locator('.tbt-home-kicker').getByText(kickerStamp, { exact: true }).waitFor();
    assert.equal(await section.locator('option:checked').textContent(), 'Top of the page');
    await pub.goto(base + '/');
    assert.ok((await pub.locator('.tbt-home-kicker').textContent()).includes(kickerStamp), 'An edited Home text must reach the public page.');
    assert.equal(await pub.locator('.tbt-home-hero h1 .tbt-home-accent--red').count(), 1, 'The Home title keeps its red accent word.');
    await page.goto(homeEdit);
    await section.waitFor({ timeout: 5000 });
    await chooseSection('Top of the page');
    await page.locator('.acf-field[data-name="home_specialist_line"] input').fill(kickerOriginal);
    await save();
    await pub.reload();
    assert.equal(await pub.locator('.tbt-home-kicker').textContent(), kickerOriginal, 'The restored Home text is back.');

    // 7. Pressing Update on a page without changing anything must never change what visitors see. WordPress saves every
    //    empty field as a blank, so any content that only had a built-in default would otherwise disappear on first save.
    await page.goto(`${base}/wp-admin/edit.php?post_type=page&post_status=publish&orderby=title&order=asc`);
    const rows = await page.locator('#the-list tr.iedit').evaluateAll((trs) => trs.map((tr) => ({
      title: tr.querySelector('a.row-title')?.textContent.trim() || '',
      edit: tr.querySelector('a.row-title')?.href || '',
      view: tr.querySelector('.row-actions .view a')?.href || '',
    })).filter((r) => r.edit && r.view));
    const signature = async (url) => {
      await pub.goto(url, { waitUntil: 'load' });
      return pub.evaluate(() => {
        const copy = document.body.cloneNode(true);
        copy.querySelectorAll('script, style, noscript').forEach((e) => e.remove());
        copy.querySelectorAll('br').forEach((br) => br.replaceWith(' ')); // a saved line break gains a newline; it looks the same
        return {
          text: copy.textContent.replace(/\s+/g, ' ').trim(),
          images: [...document.images].map((i) => i.getAttribute('src')).join('|'),
          links: [...document.querySelectorAll('a[href]')].map((a) => a.getAttribute('href')).join('|'),
          header: !!document.querySelector('.site-header__cta') && !!document.querySelector('.site-brand__logo'),
          footer: document.querySelector('.site-footer')?.textContent.replace(/\s+/g, ' ').trim(),
        };
      });
    };
    let protectedPages = 0;
    for (const row of rows) {
      await page.goto(row.edit, { waitUntil: 'load' });
      if (!(await page.locator('.tbt-page-guide').count())) continue; // a page the client created: normal editor, not ours
      protectedPages++;
      const before = await signature(row.view);
      await page.waitForSelector('#publish');
      await save();
      const after = await signature(row.view);
      for (const key of ['text', 'images', 'links', 'footer']) {
        if (after[key] !== before[key]) {
          let i = 0; while (i < before[key].length && before[key][i] === after[key][i]) i++;
          assert.fail(`Pressing Update on "${row.title}" without changes changed its ${key} at character ${i}: before "…${before[key].slice(Math.max(0, i - 40), i + 60)}…" after "…${after[key].slice(Math.max(0, i - 40), i + 60)}…"`);
        }
      }
      assert.deepEqual(after, before, `Pressing Update on "${row.title}" without changes must not change the page (${row.view}).`);
      assert.ok(after.header, `The header logo and Contact Us button must survive saving "${row.title}".`);
    }
    assert.ok(protectedPages >= 12, `All twelve protected pages must be covered, covered ${protectedPages}.`);

    // 6. Testimonials: the Class Years list counts each year's reviews with an add link that preselects the year,
    // and the Testimonials list shows where each one appears and filters to one place.
    await page.goto(`${base}/wp-admin/edit.php?post_type=tbt_class_year`);
    assert.ok(!(await page.content()).includes('critical error'), 'Class Years list loads');
    const addLink = page.locator('td.column-tbt_year_testimonials a', { hasText: 'Add testimonial' }).first();
    assert.match(await page.locator('td.column-tbt_year_testimonials').first().innerText(), /\d+ testimonials? · Add testimonial/);
    await page.goto(new URL(await addLink.getAttribute('href'), base).href);
    const placement = page.locator('[data-name="tbt_display_group"] select');
    assert.match(await placement.inputValue(), /^\d{4}$/, 'Add testimonial from a year preselects that year');
    assert.ok(await page.locator('.tbt-page-guide', { hasText: 'How to add or edit a testimonial' }).count() === 1, 'Testimonial guide is shown');
    await page.goto(`${base}/wp-admin/edit.php?post_type=tbt_testimonial&tbt_placement=notes-reviews`);
    const places = await page.locator('td.column-tbt_placement').allInnerTexts();
    assert.ok(places.length > 0 && places.every((t) => t.includes('Free Bio Notes page')), 'The place filter narrows the Testimonials list');
    assert.ok((await page.locator('td.column-tbt_kind').allInnerTexts()).every((t) => ['Video', 'Written'].includes(t.trim())), 'Type column shows Video or Written');

    assert.deepEqual(errors, [], 'No script errors in the editor: ' + errors.join('; '));
    console.log('PASS admin editing: edit screen, formatting buttons, save, new class year, Home text, saving every page changes nothing, testimonial lists and add links');
  } finally { await browser.close(); }
})().catch((e) => { console.error(process.env.TBT_STACK ? e.stack : e.message); process.exit(1); });
