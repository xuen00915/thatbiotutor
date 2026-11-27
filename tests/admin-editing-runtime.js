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

    // The editing hub brings the eight everyday pages and the privacy draft together.
    await page.getByRole('link', { name: 'Website Content', exact: true }).click();
    await page.getByRole('heading', { name: 'Website Content', exact: true }).waitFor();
    const mainPages = page.getByRole('table', { name: 'Main pages' });
    const mainPageNames = await mainPages.getByRole('link').allTextContents();
    assert.deepEqual(mainPageNames, [
      'Home', 'Classes', 'Sec 3 Pure / G3 Biology Tuition', 'Sec 4 Pure / G3 Biology Tuition',
      'Results & Testimonials', 'Free Bio Notes', 'About Us', 'Contact', 'Privacy Policy',
    ]);
    assert.match(await mainPages.getByRole('row', { name: /Privacy Policy/ }).innerText(), /Draft/);
    await page.getByText('Additional Pages', { exact: true }).click();
    const additionalPages = page.getByRole('table', { name: 'Additional pages' });
    assert.deepEqual(await additionalPages.getByRole('link').allTextContents(), [
      'Cell Division Crash Course', 'Our Causes', 'Tuition Recommendations', 'IntensiveFix',
    ]);
    for (const collection of ['Testimonials', 'Class Years', 'Tutors', 'FAQs', 'Results']) {
      assert.ok(await page.getByRole('region', { name: 'Content lists' }).getByRole('link', { name: collection, exact: true }).isVisible());
    }
    const aboutFromHub = await mainPages.getByRole('link', { name: 'About Us' }).getAttribute('href');

    // Click Update/Publish and wait for WordPress to finish saving and reload the screen.
    const save = async () => { await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), page.locator('#publish').click()]); };
    const editLink = async (title) => {
      await page.goto(`${base}/wp-admin/edit.php?post_type=page&s=${encodeURIComponent(title)}`);
      const href = await page.locator('a.row-title', { hasText: title }).first().getAttribute('href');
      return href.startsWith('http') ? href : base + href;
    };

    // 1. A protected page opens on a clear edit screen: field tabs first, no empty block canvas.
    const aboutEdit = await editLink('About Us');
    assert.equal(new URL(aboutFromHub, base).href, aboutEdit, 'The dashboard opens the real page editor.');
    await page.goto(aboutEdit);
    await page.waitForSelector('.acf-tab-button:visible');
    assert.equal(await page.locator('#editor .edit-post-layout, .block-editor-writing-flow').count(), 0, 'Protected pages must not open the empty block editor.');
    assert.ok(await page.locator('.tbt-page-guide').isVisible(), 'A short guide must explain how to edit the page.');
    assert.equal(await page.locator('#postdivrich').count(), 0, 'The unused page-body editor must be hidden.');
    const tabs = (await page.locator('.acf-tab-button:visible').allInnerTexts()).map((t) => t.trim());
    for (const tab of ['Page content', 'Top banner', 'Our Mission', 'Quality Teaching', 'Message From The Founder', 'What We Believe In', 'SEO']) assert.ok(tabs.includes(tab), `About needs the "${tab}" tab: ${tabs.join(', ')}`);

    // 2. The formatted-text toolbar is the short, client-safe one, including Highlight.
    await page.locator('.acf-tab-button:visible', { hasText: 'Our Mission' }).first().click();
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
    await page.waitForSelector('.acf-tab-button:visible');
    await page.locator('.acf-tab-button:visible', { hasText: 'Our Mission' }).first().click();
    await page.evaluate((html) => { const id = document.querySelector('.acf-field[data-name="about_mission_body"] textarea').id; tinymce.get(id).setContent(html); }, original);
    await save();
    await pub.reload();
    assert.ok(!(await pub.locator('.tbt-about-mission').innerText()).includes(stamp), 'The restored text must replace the test edit.');

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
    await page.waitForSelector('.acf-tab-button:visible');
    await page.locator('.acf-tab-button:visible', { hasText: 'Top of the page' }).first().click();
    const kicker = page.locator('.acf-field[data-name="home_specialist_line"] input');
    const kickerOriginal = await kicker.inputValue();
    const kickerStamp = 'Client kicker ' + Date.now();
    await kicker.fill(kickerStamp);
    await save();
    await pub.goto(base + '/');
    assert.ok((await pub.locator('.tbt-home-kicker').textContent()).includes(kickerStamp), 'An edited Home text must reach the public page.');
    assert.equal(await pub.locator('.tbt-home-hero h1 .tbt-home-accent--red').count(), 1, 'The Home title keeps its red accent word.');
    await page.goto(homeEdit);
    await page.waitForSelector('.acf-tab-button:visible');
    await page.locator('.acf-tab-button:visible', { hasText: 'Top of the page' }).first().click();
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
