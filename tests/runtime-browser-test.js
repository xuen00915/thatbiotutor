const { chromium } = require('playwright');

const base = process.env.TBT_RUNTIME_URL || 'http://localhost:8888';
const routes = [
  '/', '/classes/', '/sec-3-pure-g3-biology-tuition/', '/sec-4-pure-g3-biology-tuition/',
  '/testimonials/', '/notes/', '/celldivision/', '/about/', '/contact/', '/trees/',
  '/tuitionrecs/', '/intensivefix/',
];
const sizes = [
  { width: 1440, height: 900 }, { width: 768, height: 900 },
  { width: 390, height: 844 }, { width: 320, height: 700 },
];

(async () => {
  const browser = await chromium.launch({ headless: true });
  const errors = [];

  for (const viewport of sizes) {
    const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
    page.on('console', (message) => {
      const text = message.text();
      if (message.type() === 'error' && !text.includes('compute-pressure is not allowed')) {
        errors.push(`${viewport.width}: ${text}`);
      }
    });

    for (const route of routes) {
      const response = await page.goto(`${base}${route}`, { waitUntil: 'load' });
      if (!response || response.status() !== 200) throw new Error(`${route} failed at ${viewport.width}`);
      if ((await page.locator('main').innerText()).trim().length < 20) throw new Error(`${route} is blank at ${viewport.width}`);
      for (const selector of ['.site-header', 'main', '.site-footer']) {
        if (await page.locator(selector).count() !== 1) throw new Error(`${route} is missing ${selector} at ${viewport.width}`);
        const box = await page.locator(selector).boundingBox();
        if (!box || box.width < 1 || box.height < 1) throw new Error(`${route} hides ${selector} at ${viewport.width}`);
      }
      const invalidImages = await page.locator('main img').evaluateAll((images) => images.map((image) => image.currentSrc || image.src).filter((src) => !src || src.includes('static.wixstatic.com') || !src.includes('/wp-content/themes/thatbiotutor-hello-child/assets/images/files/') || !src.endsWith('.webp')));
      if (invalidImages.length) throw new Error(`${route} has invalid public image sources at ${viewport.width}: ${invalidImages.join(', ')}`);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (overflow > 1) throw new Error(`${route} overflows ${overflow}px at ${viewport.width}`);
    }

    await page.goto(base, { waitUntil: 'networkidle' });
    if (!(await page.title()).includes('ThatBioTutor')) throw new Error(`Bad Home title at ${viewport.width}`);
    const firstSection = page.locator('main [data-section]').first();
    if (await firstSection.getAttribute('data-section') !== 'hero') throw new Error(`Home hero is not the first section at ${viewport.width}`);
    const heroBox = await firstSection.boundingBox();
    const minimumHeroHeight = viewport.width >= 1000 ? 560 : 420;
    if (!heroBox || heroBox.height < minimumHeroHeight) throw new Error(`Home hero is too short at ${viewport.width}`);
    if (await firstSection.locator('.tbt-home-hero__overlay').count() !== 1) throw new Error(`Home hero overlay missing at ${viewport.width}`);
    const firstHeroImage = firstSection.locator('[data-carousel-slide]').first().locator('img');
    if ((await firstHeroImage.evaluate((image) => getComputedStyle(image).objectPosition)) !== '50% 54%') throw new Error(`Home hero focal point is wrong at ${viewport.width}`);
    const videoBox = await page.locator('[data-section="missing-key"] .tbt-video').boundingBox();
    if (!videoBox || Math.abs((videoBox.width / videoBox.height) - (16 / 9)) > 0.04) throw new Error(`Home video ratio is wrong at ${viewport.width}`);
    const schoolTrack = page.locator('[data-school-marquee] .tbt-school-marquee__track');
    if (await schoolTrack.evaluate((element) => getComputedStyle(element).animationName) !== 'none') throw new Error(`Reduced motion did not stop school marquee at ${viewport.width}`);
    if (viewport.width <= 768) {
      const toggle = page.locator('[data-menu-toggle]');
      await toggle.click();
      if (await toggle.getAttribute('aria-expanded') !== 'true') throw new Error('Mobile navigation did not open');
      await page.keyboard.press('Escape');
      if (await toggle.getAttribute('aria-expanded') !== 'false') throw new Error('Mobile navigation did not close');
    } else {
      const visibleIndex = () => page.locator('[data-carousel-slide]').evaluateAll((slides) => slides.findIndex((slide) => !slide.hidden));
      const before = await visibleIndex();
      await page.locator('[data-carousel-next]').first().click();
      if (before === await visibleIndex()) throw new Error('Carousel did not advance');
    }
    const visibleImage = page.locator('[data-carousel-slide]:not([hidden]) img');
    await visibleImage.evaluate(async (image) => {
      if (!image.complete) await new Promise((resolve) => image.addEventListener('load', resolve, { once: true }));
      if (image.decode) await image.decode();
    });
    await page.screenshot({ path: `/tmp/thatbiotutor-${viewport.width}.png`, fullPage: false });

    await page.goto(`${base}/testimonials/`, { waitUntil: 'load' });
    const testimonialFrames = page.locator('.tbt-testimonials-page iframe');
    if (await testimonialFrames.count() < 1) throw new Error(`Testimonials video missing at ${viewport.width}`);
    for (const frame of await testimonialFrames.all()) {
      if (await frame.getAttribute('scrolling') !== 'no') throw new Error(`Testimonials iframe scrolling is enabled at ${viewport.width}`);
      const wrapper = frame.locator('..');
      const box = await wrapper.boundingBox();
      if (!box || Math.abs((box.width / box.height) - (16 / 9)) > 0.04) throw new Error(`Testimonials video ratio is wrong at ${viewport.width}`);
      if (await wrapper.evaluate((element) => getComputedStyle(element).overflow) !== 'hidden') throw new Error(`Testimonials video wrapper can scroll at ${viewport.width}`);
    }

    await page.goto(`${base}/notes/`, { waitUntil: 'load' });
    const notesCarousel = page.locator('[data-section="hero"] [data-carousel]');
    if (await notesCarousel.count() !== 1) throw new Error(`Notes carousel missing at ${viewport.width}`);
    const notesStatus = notesCarousel.locator('[data-carousel-status]');
    if ((await notesStatus.textContent()).trim() !== '1 of 3') throw new Error(`Notes carousel status is wrong at ${viewport.width}`);
    await notesCarousel.locator('[data-carousel-next]').click();
    if ((await notesStatus.textContent()).trim() !== '2 of 3') throw new Error(`Notes carousel did not advance at ${viewport.width}`);

    await page.goto(`${base}/celldivision/`, { waitUntil: 'load' });
    const cellCarousel = page.locator('[data-section="hero"] [data-carousel]');
    if (await cellCarousel.count() !== 1) throw new Error(`Cell Division carousel missing at ${viewport.width}`);
    const cellStatus = cellCarousel.locator('[data-carousel-status]');
    if ((await cellStatus.textContent()).trim() !== '1 of 5') throw new Error(`Cell Division carousel status is wrong at ${viewport.width}`);

    await page.goto(`${base}/about/`, { waitUntil: 'load' });
    await page.evaluate(() => document.fonts?.ready);
    if (await page.locator('[data-section="beliefs"] img').count() !== 1) throw new Error(`About beliefs image is missing at ${viewport.width}`);
    if (viewport.width >= 1000) {
      const aboutHero = await page.locator('[data-section="hero"]').boundingBox();
      const missionBar = await page.locator('[data-section="mission"] .tbt-institutional-title-bar').boundingBox();
      const qualityBar = await page.locator('[data-section="quality"] .tbt-institutional-title-bar').boundingBox();
      if (!aboutHero || Math.abs(aboutHero.height - 321) > 1) throw new Error('About hero must preserve the original 321px desktop height');
      if (!missionBar || Math.abs(missionBar.height - 82) > 1) throw new Error('About mission bar must preserve the original 82px desktop height');
      if (!qualityBar || Math.abs(qualityBar.height - 70) > 1) throw new Error('About section bars must preserve the original 70px desktop height');
      const heroOpacity = await page.locator('.tbt-about-hero__image').evaluate((image) => getComputedStyle(image).opacity);
      if (heroOpacity !== '0.78') throw new Error(`About hero image opacity is ${heroOpacity}, expected 0.78`);
      const missionHeadingStyle = await page.locator('[data-section="mission"] .tbt-institutional-title-bar h2').evaluate((heading) => {
        const style = getComputedStyle(heading);
        return { family: style.fontFamily.toLowerCase(), size: style.fontSize, weight: style.fontWeight };
      });
      if (!missionHeadingStyle.family.includes('montserrat') || missionHeadingStyle.size !== '35px' || missionHeadingStyle.weight !== '700') {
        throw new Error(`About mission heading typography is wrong: ${JSON.stringify(missionHeadingStyle)}`);
      }
      const beliefsImage = await page.locator('[data-section="beliefs"] img').boundingBox();
      if (!beliefsImage || Math.abs(beliefsImage.width - 509) > 1 || Math.abs(beliefsImage.height - 679) > 1) throw new Error('About beliefs image must preserve the original 509x679 desktop frame');
    }

    if (viewport.width === 320) {
      await page.goto(base, { waitUntil: 'load' });
      await page.locator('main h1').first().evaluate((heading) => { heading.textContent = 'ExtremelyLongClientEditedBiologyHeadingWithoutNaturalBreaks0123456789'; });
      const quote = page.locator('.tbt-testimonial blockquote').first();
      if (await quote.count()) await quote.evaluate((element) => { element.textContent = 'LongClientEditedTestimonialWithoutNaturalBreaks0123456789'.repeat(5); });
      const editedOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (editedOverflow > 1) throw new Error(`Client-edited long content overflows ${editedOverflow}px at 320`);
      await page.goto(`${base}/about/`, { waitUntil: 'load' });
      await page.locator('.tbt-tutor img').first().evaluate((image) => image.remove());
      const missingMediaOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (missingMediaOverflow > 1) throw new Error(`Missing optional tutor media overflows ${missingMediaOverflow}px at 320`);
    }
    await page.close();
  }

  const resizePage = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await resizePage.goto(base, { waitUntil: 'load' });
  await resizePage.setViewportSize({ width: 390, height: 844 });
  await resizePage.waitForFunction(() => document.querySelector('[data-menu]')?.hidden === true);
  await resizePage.setViewportSize({ width: 1440, height: 900 });
  await resizePage.waitForFunction(() => document.querySelector('[data-menu]')?.hidden === false);
  await resizePage.close();

  await browser.close();
  if (errors.length) throw new Error(`Console errors:\n${errors.join('\n')}`);
  console.log(`PASS: ${routes.length} routes at ${sizes.map((size) => size.width).join('/')}px, shell, local WebP media, long edits, navigation, carousel, console, screenshots`);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
