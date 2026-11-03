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
  console.log(`PASS: ${routes.length} routes at ${sizes.map((size) => size.width).join('/')}px, navigation, carousel, console, screenshots`);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
