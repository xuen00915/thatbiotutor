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
    const siteFooter = page.locator('.site-footer');
    const footerText = (await siteFooter.innerText()).replace(/\s+/g, ' ').trim();
    for (const requiredFooterText of ['ThatBioTutor', "Singapore's only O Level Pure/G3 Biology specialist.", '175 Bencoolen St', '#01-45', 'Singapore 189649', '8759 7182', 'Friday – Thursday', '11am – 9:30pm', '(open all 7 days)']) {
      if (!footerText.includes(requiredFooterText)) throw new Error(`Footer is missing ${requiredFooterText} at ${viewport.width}`);
    }
    const footerPresentation = await siteFooter.evaluate((footer) => {
      const footerStyle = getComputedStyle(footer);
      const gridStyle = getComputedStyle(footer.querySelector('.site-footer__inner'));
      return { background: footerStyle.backgroundColor, columns: gridStyle.gridTemplateColumns.split(' ').length };
    });
    if (footerPresentation.background !== 'rgb(8, 37, 15)') throw new Error(`Footer background is wrong at ${viewport.width}: ${footerPresentation.background}`);
    if (viewport.width >= 1000 && footerPresentation.columns !== 4) throw new Error(`Footer is not four columns at ${viewport.width}: ${footerPresentation.columns}`);
    if (await siteFooter.locator('img[alt="ThatBioTutor"]').count() !== 1) throw new Error(`Footer logo is missing at ${viewport.width}`);
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
    if (await page.locator('[data-school-page]').count() !== 6) throw new Error(`Home school artwork count is wrong at ${viewport.width}`);
    if (await page.locator('[data-benefit-image]').count() !== 4) throw new Error(`Home benefit artwork count is wrong at ${viewport.width}`);
    if (viewport.width >= 1000) {
      await page.evaluate(() => document.fonts?.ready);
      const expectedHomeSections = {
        'hero': { y: 65, height: 724 },
        'missing-key': { y: 789, height: 840 },
        'fasttrack': { y: 1629, height: 1869 },
        'trust': { y: 3498, height: 381 },
        'hall-of-fame': { y: 3879, height: 1147 },
        'testimonials': { y: 5026, height: 800 },
        'schools': { y: 5826, height: 3390 },
        'advantage': { y: 9216, height: 2151 },
        'benefits': { y: 11367, height: 2140 },
        'causes': { y: 13507, height: 1283 },
        'tutors': { y: 14790, height: 2285 },
        'faq': { y: 17075, height: 1358 },
      };
      for (const [section, expected] of Object.entries(expectedHomeSections)) {
        const actual = await page.locator(`[data-section="${section}"]`).boundingBox();
        if (!actual || Math.abs(actual.y - expected.y) > 2 || Math.abs(actual.height - expected.height) > 2) {
          throw new Error(`Home ${section} geometry is wrong: ${JSON.stringify(actual)}`);
        }
      }
      const benefitImages = await page.locator('[data-benefit-image]').evaluateAll((images) => images.map((image) => {
        const rect = image.getBoundingClientRect();
        return { width: rect.width, height: rect.height };
      }));
      if (benefitImages.some((image) => Math.abs(image.width - 481) > 2 || Math.abs(image.height - 602) > 2)) {
        throw new Error(`Home benefit artwork geometry is wrong: ${JSON.stringify(benefitImages)}`);
      }
      const closingImage = await page.locator('[data-section="closing"] img').boundingBox();
      if (!closingImage || Math.abs(closingImage.width - 711) > 2 || Math.abs(closingImage.height - 936) > 2) {
        throw new Error(`Home closing artwork geometry is wrong: ${JSON.stringify(closingImage)}`);
      }
    }
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
    const firstStudentStory = page.locator('#class-of-2025 .tbt-testimonial--video').first();
    const firstStudentStoryText = (await firstStudentStory.innerText()).trim();
    if (!firstStudentStoryText.includes('I actually struggled a lot with answering open-ended questions') || !firstStudentStoryText.includes('Alicia [C6 to A1]')) {
      throw new Error(`Testimonials wording is disconnected from Alicia's video at ${viewport.width}`);
    }
    const firstStudentStoryLayout = await firstStudentStory.evaluate((article) => ({
      videoBeforeQuote: Boolean(article.querySelector('iframe')?.compareDocumentPosition(article.querySelector('blockquote')) & Node.DOCUMENT_POSITION_FOLLOWING),
      background: getComputedStyle(article).backgroundColor,
    }));
    if (!firstStudentStoryLayout.videoBeforeQuote) throw new Error(`Alicia's wording renders before her video at ${viewport.width}`);
    if (firstStudentStoryLayout.background !== 'rgba(0, 0, 0, 0)') throw new Error(`Alicia's transcript card has an opaque background at ${viewport.width}`);

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
    const tutorCards = page.locator('.tbt-about-tutors .tbt-tutor');
    if (await tutorCards.count() !== 4) throw new Error(`About tutor count is wrong at ${viewport.width}`);
    if (await page.locator('.tbt-about-tutors .tbt-tutor--has-documents').count() !== 2) throw new Error(`About certificate tutor count is wrong at ${viewport.width}`);
    const chelseaCertificates = tutorCards.first().locator('.tbt-tutor__certificates');
    const tutorCardSurface = await tutorCards.first().evaluate((card) => {
      const style = getComputedStyle(card);
      return { backgroundColor: style.backgroundColor, borderColor: style.borderTopColor };
    });
    if (tutorCardSurface.backgroundColor !== 'rgb(255, 255, 255)') throw new Error(`Tutor card background is not white at ${viewport.width}: ${tutorCardSurface.backgroundColor}`);
    if (tutorCardSurface.borderColor === 'rgba(0, 0, 0, 0)' || tutorCardSurface.borderColor === 'transparent') throw new Error(`Tutor card green border is missing at ${viewport.width}`);
    if (await chelseaCertificates.locator('[data-carousel-slide]').count() !== 2) throw new Error(`Chelsea certificate carousel is incomplete at ${viewport.width}`);
    if (await tutorCards.nth(1).locator('.tbt-tutor__certificates').count() !== 0 || await tutorCards.nth(3).locator('.tbt-tutor__certificates').count() !== 0) throw new Error(`About tutor cards render empty certificate columns at ${viewport.width}`);
    const chelseaCertificateStatus = chelseaCertificates.locator('[data-carousel-status]');
    if ((await chelseaCertificateStatus.textContent()).trim() !== '1 of 2') throw new Error(`Chelsea certificate status is wrong at ${viewport.width}`);
    await chelseaCertificates.locator('[data-carousel-next]').click();
    if ((await chelseaCertificateStatus.textContent()).trim() !== '2 of 2') throw new Error(`Chelsea certificate controls failed at ${viewport.width}`);
    const visibleCertificateTrigger = chelseaCertificates.locator('[data-carousel-slide]:not([hidden]) [data-certificate-lightbox-trigger]');
    await visibleCertificateTrigger.click();
    const certificateLightbox = page.locator('.tbt-certificate-lightbox');
    if (!await certificateLightbox.isVisible()) throw new Error(`Certificate lightbox did not open at ${viewport.width}`);
    const lightboxBackground = await certificateLightbox.evaluate((dialog) => getComputedStyle(dialog).backgroundColor);
    if (lightboxBackground !== 'rgb(255, 255, 255)') throw new Error(`Certificate lightbox background is not white at ${viewport.width}: ${lightboxBackground}`);
    await page.addStyleTag({ content: 'body button:hover, body button:focus, body button:active { background-color: rgb(190, 61, 103) !important; color: #fff !important; }' });
    const closeControlBackground = await certificateLightbox.locator('[data-certificate-lightbox-close]').evaluate((button) => getComputedStyle(button).backgroundColor);
    if (closeControlBackground === 'rgb(190, 61, 103)') throw new Error(`Parent theme pink leaked into the certificate close control at ${viewport.width}`);
    const enlargedCertificate = certificateLightbox.locator('img');
    if (!await enlargedCertificate.evaluate((image) => image.complete && image.naturalWidth > 0)) throw new Error(`Certificate lightbox image did not load at ${viewport.width}`);
    const enlargedSecondSource = await enlargedCertificate.getAttribute('src');
    const lightboxPrevious = certificateLightbox.locator('[data-certificate-lightbox-prev]');
    const lightboxNext = certificateLightbox.locator('[data-certificate-lightbox-next]');
    if (!await lightboxPrevious.isVisible() || !await lightboxNext.isVisible()) throw new Error(`Multi-certificate lightbox controls are missing at ${viewport.width}`);
    await lightboxPrevious.click();
    const previousControlBackground = await lightboxPrevious.evaluate((button) => getComputedStyle(button).backgroundColor);
    if (previousControlBackground === 'rgb(190, 61, 103)') throw new Error(`Parent theme pink leaked into the certificate navigation at ${viewport.width}`);
    const enlargedFirstSource = await enlargedCertificate.getAttribute('src');
    if (!enlargedFirstSource || enlargedFirstSource === enlargedSecondSource) throw new Error(`Lightbox previous control did not change the certificate at ${viewport.width}`);
    await lightboxNext.click();
    if (await enlargedCertificate.getAttribute('src') !== enlargedSecondSource) throw new Error(`Lightbox next control did not restore the certificate at ${viewport.width}`);
    const lightboxGeometry = await certificateLightbox.evaluate((dialog) => {
      const dialogRect = dialog.getBoundingClientRect();
      const imageRect = dialog.querySelector('img')?.getBoundingClientRect();
      return { dialogTop: dialogRect.top, dialogBottom: dialogRect.bottom, imageTop: imageRect?.top, imageBottom: imageRect?.bottom };
    });
    if (lightboxGeometry.imageTop < lightboxGeometry.dialogTop || lightboxGeometry.imageBottom > lightboxGeometry.dialogBottom) throw new Error(`Certificate is clipped in the lightbox at ${viewport.width}: ${JSON.stringify(lightboxGeometry)}`);
    await certificateLightbox.locator('[data-certificate-lightbox-close]').click();
    if (await certificateLightbox.isVisible()) throw new Error(`Certificate lightbox did not close at ${viewport.width}`);
    if (!await visibleCertificateTrigger.evaluate((trigger) => trigger === document.activeElement)) throw new Error(`Certificate lightbox did not restore focus at ${viewport.width}`);
    const singleCertificateTrigger = tutorCards.nth(2).locator('[data-certificate-lightbox-trigger]');
    await singleCertificateTrigger.click();
    if (await lightboxPrevious.isVisible() || await lightboxNext.isVisible()) throw new Error(`Single-certificate lightbox shows unnecessary controls at ${viewport.width}`);
    await certificateLightbox.locator('[data-certificate-lightbox-close]').click();
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
      const chelseaColumns = await tutorCards.first().evaluate((card) => {
        const portrait = card.querySelector('.tbt-tutor__visual')?.getBoundingClientRect();
        const details = card.querySelector('.tbt-tutor__details')?.getBoundingClientRect();
        const certificates = card.querySelector('.tbt-tutor__certificates')?.getBoundingClientRect();
        return { portraitX: portrait?.x, detailsX: details?.x, certificatesX: certificates?.x };
      });
      if (!(chelseaColumns.portraitX < chelseaColumns.detailsX && chelseaColumns.detailsX < chelseaColumns.certificatesX)) throw new Error(`About tutor columns are not ordered at ${viewport.width}: ${JSON.stringify(chelseaColumns)}`);
    } else {
      const chelseaStack = await tutorCards.first().evaluate((card) => {
        const portrait = card.querySelector('.tbt-tutor__visual')?.getBoundingClientRect();
        const details = card.querySelector('.tbt-tutor__details')?.getBoundingClientRect();
        const certificates = card.querySelector('.tbt-tutor__certificates')?.getBoundingClientRect();
        return { portraitY: portrait?.y, detailsY: details?.y, certificatesY: certificates?.y };
      });
      if (!(chelseaStack.portraitY < chelseaStack.detailsY && chelseaStack.detailsY < chelseaStack.certificatesY)) throw new Error(`About tutor columns do not stack at ${viewport.width}: ${JSON.stringify(chelseaStack)}`);
    }

    if (viewport.width >= 1000) {
      await page.goto(`${base}/contact/`, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      const contactHeading = await page.locator('.tbt-contact-content h1').boundingBox();
      const contactHeadingStyle = await page.locator('.tbt-contact-content h1').evaluate((heading) => {
        const style = getComputedStyle(heading);
        return { family: style.fontFamily.toLowerCase(), size: style.fontSize, weight: style.fontWeight };
      });
      const contactButton = await page.locator('.tbt-contact-content .tbt-institutional-button').boundingBox();
      if (!contactHeading || Math.abs(contactHeading.y - 108) > 1 || Math.abs(contactHeading.height - 73) > 1) throw new Error(`Contact heading geometry is wrong: ${JSON.stringify(contactHeading)}`);
      if (!contactHeadingStyle.family.includes('montserrat') || contactHeadingStyle.size !== '60px' || contactHeadingStyle.weight !== '700') throw new Error(`Contact heading typography is wrong: ${JSON.stringify(contactHeadingStyle)}`);
      if (!contactButton || Math.abs(contactButton.y - 528) > 1 || Math.abs(contactButton.width - 328) > 1 || Math.abs(contactButton.height - 69) > 1) throw new Error(`Contact button geometry is wrong: ${JSON.stringify(contactButton)}`);

      await page.goto(`${base}/trees/`, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      const causesBar = await page.locator('[data-section="planting"] .tbt-institutional-title-bar').boundingBox();
      const causesImages = await page.locator('.tbt-causes-section__media img').evaluateAll((images) => images.map((image) => {
        const rect = image.getBoundingClientRect();
        return { y: rect.y, width: rect.width, height: rect.height, fit: getComputedStyle(image).objectFit };
      }));
      if (!causesBar || Math.abs(causesBar.height - 70) > 1) throw new Error(`Causes title bar geometry is wrong: ${JSON.stringify(causesBar)}`);
      const expectedCausesImages = [
        { y: 135, width: 550, height: 550 },
        { y: 975, width: 654, height: 502 },
        { y: 1793, width: 719, height: 589 },
      ];
      for (const [index, expected] of expectedCausesImages.entries()) {
        const actual = causesImages[index];
        if (!actual || Math.abs(actual.y - expected.y) > 1 || Math.abs(actual.width - expected.width) > 1 || Math.abs(actual.height - expected.height) > 1 || actual.fit !== 'cover') {
          throw new Error(`Causes image ${index + 1} geometry is wrong: ${JSON.stringify(actual)}`);
        }
      }

      await page.goto(`${base}/tuitionrecs/`, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      const recommendationsPage = await page.locator('.tbt-recommendations-page').boundingBox();
      const recommendationsBar = await page.locator('.tbt-institutional-title-bar').boundingBox();
      const recommendationsHeading = page.locator('.tbt-institutional-title-bar h1');
      const recommendationsHeadingStyle = await recommendationsHeading.evaluate((heading) => {
        const style = getComputedStyle(heading);
        return { family: style.fontFamily.toLowerCase(), size: style.fontSize, weight: style.fontWeight, line: style.lineHeight };
      });
      const recommendationsFirstParagraph = await page.locator('.tbt-recommendations-copy p').first().boundingBox();
      if ((await recommendationsHeading.textContent()).trim() !== 'Tuition Recommendations') throw new Error('Recommendations heading does not match the public page');
      if (!recommendationsPage || Math.abs(recommendationsPage.height - 852) > 1) throw new Error(`Recommendations page height is wrong: ${JSON.stringify(recommendationsPage)}`);
      if (!recommendationsBar || Math.abs(recommendationsBar.height - 84) > 1) throw new Error(`Recommendations title bar geometry is wrong: ${JSON.stringify(recommendationsBar)}`);
      if (!recommendationsHeadingStyle.family.includes('montserrat') || recommendationsHeadingStyle.size !== '40px' || recommendationsHeadingStyle.weight !== '700' || recommendationsHeadingStyle.line !== '60px') throw new Error(`Recommendations heading typography is wrong: ${JSON.stringify(recommendationsHeadingStyle)}`);
      if (!recommendationsFirstParagraph || Math.abs(recommendationsFirstParagraph.y - 198) > 1) throw new Error(`Recommendations copy starts in the wrong position: ${JSON.stringify(recommendationsFirstParagraph)}`);
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

  const motionPage = await browser.newPage({ viewport: { width: 1440, height: 900 }, reducedMotion: 'no-preference' });
  await motionPage.goto(`${base}/about/`, { waitUntil: 'load' });
  const certificateCarousel = motionPage.locator('.tbt-tutor__certificates [data-carousel]').first();
  const certificateStatus = certificateCarousel.locator('[data-carousel-status]');
  await certificateCarousel.hover();
  await motionPage.waitForTimeout(4700);
  if ((await certificateStatus.textContent()).trim() !== '1 of 2') throw new Error('Certificate autoplay must pause on hover');
  await motionPage.mouse.move(0, 0);
  await motionPage.waitForTimeout(4700);
  if ((await certificateStatus.textContent()).trim() !== '2 of 2') throw new Error('Certificate autoplay did not resume after hover');
  await motionPage.close();

  await browser.close();
  if (errors.length) throw new Error(`Console errors:\n${errors.join('\n')}`);
  console.log(`PASS: ${routes.length} routes at ${sizes.map((size) => size.width).join('/')}px, shell, local WebP media, long edits, navigation, carousel, console, screenshots`);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
