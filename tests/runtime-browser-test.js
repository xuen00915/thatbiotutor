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
      const inner = footer.querySelector('.site-footer__inner');
      const gridStyle = getComputedStyle(inner);
      const logo = footer.querySelector('.site-footer__logo');
      const brand = footer.querySelector('.site-footer__brand-link span');
      const heading = footer.querySelector('.site-footer__column h2');
      return {
        background: footerStyle.backgroundColor,
        height: footer.getBoundingClientRect().height,
        paddingTop: parseFloat(footerStyle.paddingTop),
        paddingBottom: parseFloat(footerStyle.paddingBottom),
        innerWidth: inner.getBoundingClientRect().width,
        isHome: document.body.classList.contains('home'),
        columns: gridStyle.gridTemplateColumns.split(' ').length,
        logoWidth: logo.getBoundingClientRect().width,
        brandFont: parseFloat(getComputedStyle(brand).fontSize),
        headingFont: parseFloat(getComputedStyle(heading).fontSize),
      };
    });
    if (footerPresentation.background !== 'rgb(6, 38, 18)') throw new Error(`Footer background is wrong at ${viewport.width}: ${footerPresentation.background}`);
    if (footerPresentation.paddingTop !== 48 || footerPresentation.paddingBottom !== 48) throw new Error(`Footer padding is outside the Inquire reference at ${viewport.width}: ${JSON.stringify(footerPresentation)}`);
    if (footerPresentation.logoWidth !== 36 || footerPresentation.brandFont !== 18 || footerPresentation.headingFont !== 12) throw new Error(`Footer typography is outside the Inquire reference at ${viewport.width}: ${JSON.stringify(footerPresentation)}`);
    if (viewport.width >= 768 && footerPresentation.columns !== 4) throw new Error(`Footer is not four columns at ${viewport.width}: ${footerPresentation.columns}`);
    if (viewport.width < 768 && footerPresentation.columns !== 3) throw new Error(`Footer is not three columns at ${viewport.width}: ${footerPresentation.columns}`);
    const footerHeightRange = viewport.width >= 768 ? [250, 285] : viewport.width >= 360 ? [350, 430] : [430, 480];
    if (footerPresentation.height < footerHeightRange[0] || footerPresentation.height > footerHeightRange[1]) throw new Error(`Footer height is outside the Inquire reference at ${viewport.width}: ${footerPresentation.height}`);
    if (viewport.width >= 1200 && Math.abs(footerPresentation.innerWidth - (footerPresentation.isHome ? 944 : 1120)) > 1) throw new Error(`Footer content width is outside the Inquire reference at ${viewport.width}: ${footerPresentation.innerWidth}`);
    if (await siteFooter.locator('img[alt="ThatBioTutor"]').count() !== 1) throw new Error(`Footer logo is missing at ${viewport.width}`);
    const firstSection = page.locator('main [data-section]').first();
    if (await firstSection.getAttribute('data-section') !== 'hero') throw new Error(`Home hero is not the first section at ${viewport.width}`);
    const heroBox = await firstSection.boundingBox();
    const heroHeightRange = viewport.width >= 1000 ? [740, 1200] : viewport.width <= 520 ? [600, 800] : [740, 1000];
    if (!heroBox || heroBox.height < heroHeightRange[0] || heroBox.height > heroHeightRange[1]) throw new Error(`Home hero is outside the reference scale at ${viewport.width}: ${JSON.stringify(heroBox)}`);
    if (await firstSection.locator('.tbt-home-hero__copy').count() !== 1) throw new Error(`Home hero copy missing at ${viewport.width}`);
    const homeScale = await page.locator('.tbt-home-inquire').evaluate((home) => {
      const heroHeading = home.querySelector('h1');
      const heroKicker = home.querySelector('.tbt-home-kicker');
      const heroResult = home.querySelector('.tbt-home-hero__result');
      const heroRefund = home.querySelector('.tbt-home-hero__refund');
      const heroButton = home.querySelector('.tbt-home-hero .tbt-home-button');
      const sectionHeading = home.querySelector('[data-section="track-record"] h2');
      const carousel = home.querySelector('.tbt-home-hero__carousel');
      const carouselDots = [...home.querySelectorAll('.tbt-home-hero [data-carousel-dot]')];
      const carouselControl = home.querySelector('.tbt-home-hero [data-carousel-prev]');
      const problemQuotes = home.querySelector('.tbt-home-problem__quotes');
      const problemProof = home.querySelector('.tbt-home-problem__proof');
      const problemQuote = problemQuotes.querySelector('blockquote');
      const problemBridges = [...home.querySelectorAll('.tbt-home-problem__bridge')];
      const problemCard = problemProof.querySelector('figure');
      const problemSecondCard = problemProof.querySelector('figure:nth-child(2)');
      const fasttrackContainer = home.querySelector('[data-section="fasttrack"] > .tbt-home-container');
      const fasttrackCard = home.querySelector('.tbt-home-fasttrack__comparison');
      const fasttrackHeader = fasttrackCard.querySelector('h3');
      const fasttrackColumns = [...fasttrackCard.querySelectorAll(':scope > div')];
      const proofContainer = home.querySelector('[data-section="proof"] > .tbt-home-container');
      const proofVideos = home.querySelector('.tbt-home-proof__videos');
      const proofSchools = home.querySelector('.tbt-home-proof__schools-copy');
      const proofCrests = home.querySelector('.tbt-home-proof__crests');
      const firstProofCrest = proofCrests.querySelector('img');
      const proofResultsCopy = home.querySelector('.tbt-home-proof__results-copy');
      const proofResults = home.querySelector('.tbt-home-proof__results');
      const proofResultImages = [...proofResults.querySelectorAll('img')];
      const statsPhotos = home.querySelector('.tbt-home-stats__photos');
      const tutors = home.querySelector('.tbt-home-tutors__grid');
      const sectionKeys = ['problem', 'track-record', 'fasttrack', 'proof', 'tutors', 'quality', 'practical', 'guarantee', 'fit', 'founder', 'faq', 'classroom', 'closing'];
      const rect = (element) => element.getBoundingClientRect();
      const columnCount = (element) => getComputedStyle(element).gridTemplateColumns.split(' ').length;
      const style = (element) => getComputedStyle(element);
      return {
        heroFont: parseFloat(getComputedStyle(heroHeading).fontSize),
        heroFamily: style(heroHeading).fontFamily,
        heroLineHeight: parseFloat(style(heroHeading).lineHeight),
        heroLetterSpacing: style(heroHeading).letterSpacing,
        kickerFont: parseFloat(style(heroKicker).fontSize),
        kickerWeight: style(heroKicker).fontWeight,
        resultFont: parseFloat(style(heroResult).fontSize),
        resultLineHeight: parseFloat(style(heroResult).lineHeight),
        refundFont: parseFloat(style(heroRefund).fontSize),
        refundLineHeight: parseFloat(style(heroRefund).lineHeight),
        buttonFont: parseFloat(style(heroButton).fontSize),
        buttonWeight: style(heroButton).fontWeight,
        buttonHeight: rect(heroButton).height,
        buttonRadius: parseFloat(style(heroButton).borderRadius),
        buttonBackground: style(heroButton).backgroundColor,
        sectionFont: parseFloat(getComputedStyle(sectionHeading).fontSize),
        carouselRatio: rect(carousel).width / rect(carousel).height,
        carouselDotCount: carouselDots.length,
        carouselDots: carouselDots.map((dot) => ({
          width: rect(dot).width,
          height: rect(dot).height,
          radius: style(dot).borderRadius,
          background: style(dot).backgroundColor,
        })),
        carouselControl: {
          width: rect(carouselControl).width,
          height: rect(carouselControl).height,
          background: style(carouselControl).backgroundColor,
          borderWidth: style(carouselControl).borderWidth,
        },
        problem: {
          bridgeText: problemBridges.map((bridge) => bridge.textContent.trim()),
          quoteColumns: columnCount(problemQuotes),
          proofColumns: columnCount(problemProof),
          quotesWidth: rect(problemQuotes).width,
          proofWidth: rect(problemProof).width,
          quoteRadius: parseFloat(style(problemQuote).borderRadius),
          quoteAlign: style(problemQuote).textAlign,
          quoteFont: parseFloat(style(problemQuote).fontSize),
          quoteLineHeight: parseFloat(style(problemQuote).lineHeight),
          cardPadding: parseFloat(style(problemCard).paddingTop),
          cardRadius: parseFloat(style(problemCard).borderRadius),
          cardBorderWidth: parseFloat(style(problemCard).borderTopWidth),
          secondCardTransform: style(problemSecondCard).transform,
          firstBridge: problemBridges[0] ? {
            size: parseFloat(style(problemBridges[0]).fontSize),
            weight: style(problemBridges[0]).fontWeight,
            lineHeight: parseFloat(style(problemBridges[0]).lineHeight),
            align: style(problemBridges[0]).textAlign,
          } : null,
          secondBridge: problemBridges[1] ? {
            size: parseFloat(style(problemBridges[1]).fontSize),
            lineHeight: parseFloat(style(problemBridges[1]).lineHeight),
            align: style(problemBridges[1]).textAlign,
          } : null,
        },
        fasttrack: {
          containerWidth: rect(fasttrackContainer).width,
          containerPadding: parseFloat(style(fasttrackContainer).paddingLeft),
          cardWidth: rect(fasttrackCard).width,
          cardColumns: columnCount(fasttrackCard),
          cardBackground: style(fasttrackCard).backgroundColor,
          cardBorderWidth: parseFloat(style(fasttrackCard).borderTopWidth),
          cardRadius: parseFloat(style(fasttrackCard).borderRadius),
          headerWidth: rect(fasttrackHeader).width,
          headerHeight: rect(fasttrackHeader).height,
          headerPadding: parseFloat(style(fasttrackHeader).paddingTop),
          headerBackground: style(fasttrackHeader).backgroundColor,
          headerCounterCount: fasttrackHeader.querySelectorAll('span').length,
          columnBackgrounds: fasttrackColumns.map((column) => style(column).backgroundColor),
          mediaCount: home.querySelectorAll('.tbt-home-fasttrack__tbt img').length,
        },
        proof: {
          containerWidth: rect(proofContainer).width,
          containerPadding: parseFloat(style(proofContainer).paddingLeft),
          eyebrowCount: home.querySelectorAll('[data-section="proof"] > .tbt-home-container > .tbt-home-eyebrow').length,
          videoWidth: rect(proofVideos).width,
          videoColumns: columnCount(proofVideos),
          videoGap: parseFloat(style(proofVideos).gap),
          schoolsSize: parseFloat(style(proofSchools).fontSize),
          schoolsLineHeight: parseFloat(style(proofSchools).lineHeight),
          crestDisplay: style(proofCrests).display,
          crestOverflow: style(proofCrests).overflowX,
          crestWidth: rect(firstProofCrest).width,
          crestRows: new Set([...proofCrests.querySelectorAll('img')].map((crest) => Math.round(rect(crest).top))).size,
          resultsCopy: proofResultsCopy?.textContent.trim() || '',
          resultsCopySize: proofResultsCopy ? parseFloat(style(proofResultsCopy).fontSize) : 0,
          resultsCopyLineHeight: proofResultsCopy ? parseFloat(style(proofResultsCopy).lineHeight) : 0,
          resultImageCount: proofResultImages.length,
          resultMediaKey: proofResultImages[0]?.getAttribute('data-media-key') || '',
          resultWidth: proofResultImages[0] ? rect(proofResultImages[0]).width : 0,
          resultRatio: proofResultImages[0] ? rect(proofResultImages[0]).width / rect(proofResultImages[0]).height : 0,
        },
        statsPhotoColumns: columnCount(statsPhotos),
        tutorColumns: columnCount(tutors),
        proofResultColumns: columnCount(proofResults),
        sections: Object.fromEntries(sectionKeys.map((key) => {
          const section = home.querySelector(`[data-section="${key}"]`);
          const heading = section.querySelector('h2');
          return [key, {
            background: style(section).backgroundColor,
            heading: heading ? {
              size: parseFloat(style(heading).fontSize),
              lineHeight: parseFloat(style(heading).lineHeight),
              align: style(heading).textAlign,
              spacing: style(heading).letterSpacing,
            } : null,
          }];
        })),
      };
    });
    const maximumHeroFont = viewport.width >= 768 ? 61 : viewport.width <= 520 ? 37 : 49;
    const maximumSectionFont = viewport.width >= 1000 ? 37 : viewport.width <= 520 ? 31 : 35;
    if (homeScale.heroFont > maximumHeroFont) throw new Error(`Home hero heading exceeds the reference scale at ${viewport.width}: ${homeScale.heroFont}px`);
    const expectedHeroTypography = viewport.width <= 520
      ? { hero: 36, heroLine: 45, kicker: 14, result: 18, resultLine: 28, refund: 14, refundLine: 20 }
      : { hero: 60, heroLine: 75, kicker: 16, result: 24, resultLine: 32, refund: 16, refundLine: 24 };
    if (!homeScale.heroFamily.startsWith('Poppins')) throw new Error(`Home does not use the Inquire Poppins family at ${viewport.width}: ${homeScale.heroFamily}`);
    if (homeScale.heroFont !== expectedHeroTypography.hero || homeScale.heroLineHeight !== expectedHeroTypography.heroLine || homeScale.heroLetterSpacing !== 'normal') throw new Error(`Home hero typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    if (homeScale.kickerFont !== expectedHeroTypography.kicker || homeScale.kickerWeight !== '600') throw new Error(`Home kicker typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    if (homeScale.resultFont !== expectedHeroTypography.result || homeScale.resultLineHeight !== expectedHeroTypography.resultLine) throw new Error(`Home result typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    if (homeScale.refundFont !== expectedHeroTypography.refund || homeScale.refundLineHeight !== expectedHeroTypography.refundLine) throw new Error(`Home refund typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    if (homeScale.buttonFont !== 18 || homeScale.buttonWeight !== '700' || homeScale.buttonHeight !== 60 || homeScale.buttonRadius < 9999 || homeScale.buttonBackground !== 'rgb(67, 180, 58)') throw new Error(`Home CTA differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    if (homeScale.carouselDotCount !== 5 || homeScale.carouselDots.some((dot) => dot.width !== 6 || dot.height !== 6 || dot.radius !== '50%' || !['rgb(47, 138, 39)', 'rgb(255, 255, 255)'].includes(dot.background))) throw new Error(`Home carousel dots differ from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.carouselDots)}`);
    if (homeScale.carouselControl.width !== 30 || homeScale.carouselControl.height !== 30 || homeScale.carouselControl.background !== 'rgba(0, 0, 0, 0)' || homeScale.carouselControl.borderWidth !== '0px') throw new Error(`Home carousel control differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.carouselControl)}`);
    const expectedProblemWidth = Math.min(944, viewport.width - 32);
    const expectedProblemTypography = viewport.width <= 520
      ? { quote: 16, quoteLine: 24, bridge: 20, bridgeLine: 28 }
      : { quote: 18, quoteLine: 28, bridge: 24, bridgeLine: 32 };
    if (JSON.stringify(homeScale.problem.bridgeText) !== JSON.stringify(['Sound familiar? Many TBT students started exactly here.', "But by the end of the year, they’re telling me:"])) throw new Error(`Home problem transitions are missing at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    if (homeScale.problem.quoteColumns !== 1 || homeScale.problem.proofColumns !== 1 || Math.abs(homeScale.problem.quotesWidth - expectedProblemWidth) > 1 || Math.abs(homeScale.problem.proofWidth - expectedProblemWidth) > 1) throw new Error(`Home problem layout differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    if (homeScale.problem.quoteRadius !== 18 || homeScale.problem.quoteAlign !== 'center' || homeScale.problem.quoteFont !== expectedProblemTypography.quote || homeScale.problem.quoteLineHeight !== expectedProblemTypography.quoteLine) throw new Error(`Home problem prompts differ from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    if (!homeScale.problem.firstBridge || homeScale.problem.firstBridge.size !== expectedProblemTypography.bridge || homeScale.problem.firstBridge.weight !== '700' || homeScale.problem.firstBridge.lineHeight !== expectedProblemTypography.bridgeLine || homeScale.problem.firstBridge.align !== 'center') throw new Error(`Home first problem transition differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    if (!homeScale.problem.secondBridge || homeScale.problem.secondBridge.size !== 18 || homeScale.problem.secondBridge.lineHeight !== 28 || homeScale.problem.secondBridge.align !== 'center') throw new Error(`Home second problem transition differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    if (homeScale.problem.cardPadding !== 24 || homeScale.problem.cardRadius !== 18 || homeScale.problem.cardBorderWidth !== 0 || homeScale.problem.secondCardTransform !== 'none') throw new Error(`Home problem testimonials differ from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.problem)}`);
    const expectedFeatureContainerWidth = Math.min(944, viewport.width - 32);
    const expectedFeatureContentWidth = expectedFeatureContainerWidth;
    const expectedFasttrackCardWidth = Math.min(944, expectedFeatureContainerWidth);
    const expectedFasttrackColumns = viewport.width <= 520 ? 1 : 2;
    if (homeScale.fasttrack.containerWidth !== expectedFeatureContainerWidth || homeScale.fasttrack.containerPadding !== 0 || homeScale.fasttrack.cardWidth !== Math.min(944, expectedFeatureContainerWidth)) throw new Error(`Home FastTrack width differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.fasttrack)}`);
    if (homeScale.fasttrack.cardColumns !== expectedFasttrackColumns || homeScale.fasttrack.cardBackground !== 'rgb(255, 255, 255)' || homeScale.fasttrack.cardBorderWidth !== 0 || homeScale.fasttrack.cardRadius !== 18) throw new Error(`Home FastTrack card differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.fasttrack)}`);
    if (Math.abs(homeScale.fasttrack.headerWidth - expectedFasttrackCardWidth) > 1 || homeScale.fasttrack.headerHeight !== 52 || homeScale.fasttrack.headerPadding !== 12 || homeScale.fasttrack.headerBackground !== 'rgb(67, 180, 58)' || homeScale.fasttrack.headerCounterCount !== 0) throw new Error(`Home FastTrack header differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.fasttrack)}`);
    if (homeScale.fasttrack.columnBackgrounds[0] !== 'rgba(0, 0, 0, 0)' || homeScale.fasttrack.columnBackgrounds[1] !== 'rgba(240, 248, 238, 0.6)' || homeScale.fasttrack.mediaCount !== 5) throw new Error(`Home FastTrack comparison content differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.fasttrack)}`);
    const expectedProofColumns = viewport.width < 768 ? 1 : 2;
    const expectedSchoolsTypography = viewport.width <= 520 ? { size: 20, line: 28 } : { size: 24, line: 32 };
    const expectedCrestWidth = viewport.width >= 1025
      ? (expectedFeatureContentWidth - (7 * 35)) / 8
      : viewport.width >= 768
        ? (expectedFeatureContentWidth - 35) / 2
        : (expectedFeatureContentWidth - 30) / 4;
    if (homeScale.proof.containerWidth !== expectedFeatureContainerWidth || homeScale.proof.containerPadding !== 0 || homeScale.proof.videoWidth !== expectedFeatureContentWidth || homeScale.proof.videoColumns !== expectedProofColumns || homeScale.proof.videoGap !== 24) throw new Error(`Home proof layout differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.proof)}`);
    if (homeScale.proof.eyebrowCount !== 0 || homeScale.proof.schoolsSize !== expectedSchoolsTypography.size || homeScale.proof.schoolsLineHeight !== expectedSchoolsTypography.line) throw new Error(`Home proof typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.proof)}`);
    if (homeScale.proof.crestDisplay !== 'flex' || homeScale.proof.crestOverflow !== 'hidden' || Math.abs(homeScale.proof.crestWidth - expectedCrestWidth) > 1 || homeScale.proof.crestRows !== 1) throw new Error(`Home school carousel differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.proof)}`);
    const expectedProofResultWidth = expectedFeatureContentWidth;
    if (homeScale.proof.resultsCopy !== 'Some of our biggest grade jumps. There are many more.' || homeScale.proof.resultsCopySize !== expectedSchoolsTypography.size || homeScale.proof.resultsCopyLineHeight !== expectedSchoolsTypography.line) throw new Error(`Home proof result copy differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.proof)}`);
    if (homeScale.proof.resultImageCount !== 1 || homeScale.proof.resultMediaKey !== 'curated_shared_hall_of_fame' || Math.abs(homeScale.proof.resultWidth - expectedProofResultWidth) > 1 || Math.abs(homeScale.proof.resultRatio - (940 / 791)) > 0.01) throw new Error(`Home Hall of Fame differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.proof)}`);
    const lightSections = ['problem', 'track-record', 'proof', 'quality', 'practical', 'fit', 'faq', 'classroom'];
    const softGreenSections = ['tutors', 'founder'];
    const darkSections = ['fasttrack', 'guarantee', 'closing'];
    if (lightSections.some((key) => homeScale.sections[key].background !== 'rgb(255, 255, 255)')) throw new Error(`Home light section sequence differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    if (softGreenSections.some((key) => homeScale.sections[key].background !== 'rgb(240, 248, 238)')) throw new Error(`Home soft-green section sequence differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    if (darkSections.some((key) => homeScale.sections[key].background !== 'rgb(14, 55, 29)')) throw new Error(`Home dark section sequence differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    const centeredSections = ['fasttrack', 'proof', 'tutors', 'quality', 'practical', 'guarantee', 'faq', 'classroom'];
    if (centeredSections.some((key) => homeScale.sections[key].heading?.align !== 'center')) throw new Error(`Home section heading alignment differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    const compactHeadingSize = viewport.width <= 520 ? 24 : 30;
    const standardHeadingSize = viewport.width <= 520 ? 30 : 36;
    if (['track-record', 'practical', 'classroom'].some((key) => homeScale.sections[key].heading?.size !== compactHeadingSize)) throw new Error(`Home compact section headings differ from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    if (['fasttrack', 'proof', 'tutors', 'quality', 'guarantee', 'founder', 'faq'].some((key) => homeScale.sections[key].heading?.size !== standardHeadingSize)) throw new Error(`Home standard section headings differ from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    if (homeScale.sections.fit.heading?.size !== 24 || Object.values(homeScale.sections).filter((section) => section.heading).some((section) => section.heading.spacing !== 'normal')) throw new Error(`Home section typography differs from Inquire at ${viewport.width}: ${JSON.stringify(homeScale.sections)}`);
    if (homeScale.sectionFont > maximumSectionFont) throw new Error(`Home section heading exceeds the reference scale at ${viewport.width}: ${homeScale.sectionFont}px`);
    if (Math.abs(homeScale.carouselRatio - 3) > 0.1) throw new Error(`Home hero carousel is outside the reference 3:1 ratio at ${viewport.width}: ${homeScale.carouselRatio}`);
    if (viewport.width <= 520 && (homeScale.statsPhotoColumns !== 2 || homeScale.tutorColumns !== 2)) {
      throw new Error(`Home mobile grids are too large at ${viewport.width}: ${JSON.stringify(homeScale)}`);
    }
    const firstHeroImage = firstSection.locator('[data-carousel-slide]').first().locator('img');
    if ((await firstHeroImage.evaluate((image) => getComputedStyle(image).objectPosition)) !== '50% 58%') throw new Error(`Home hero focal point is wrong at ${viewport.width}`);
    const videoBox = await page.locator('[data-section="track-record"] .tbt-home-video').boundingBox();
    if (!videoBox || Math.abs((videoBox.width / videoBox.height) - (16 / 9)) > 0.04) throw new Error(`Home video ratio is wrong at ${viewport.width}`);
    if (await page.locator('[data-section="track-record"] [data-media-key^="home_number_"]').count() !== 4) throw new Error(`Home number-photo count is wrong at ${viewport.width}`);
    if (await page.locator('[data-section="classroom"] [data-media-key^="home_classroom_"]').count() !== 3) throw new Error(`Home classroom-photo count is wrong at ${viewport.width}`);
    if (await page.locator('[data-section="guarantee"] form').count() !== 0) throw new Error(`Home guarantee still contains a form at ${viewport.width}`);
    if (await page.locator('[data-section="guarantee"] a[href^="https://wa.me/"]').count() !== 1) throw new Error(`Home guarantee WhatsApp CTA is missing at ${viewport.width}`);
    const homeSections = await page.locator('main [data-section]').evaluateAll((sections) => sections.map((section) => section.getAttribute('data-section')));
    const expectedHomeSections = ['hero', 'problem', 'track-record', 'fasttrack', 'proof', 'tutors', 'quality', 'practical', 'guarantee', 'fit', 'founder', 'faq', 'classroom', 'closing'];
    if (JSON.stringify(homeSections) !== JSON.stringify(expectedHomeSections)) throw new Error(`Home section order is wrong at ${viewport.width}: ${JSON.stringify(homeSections)}`);
    const countUps = page.locator('[data-count-up]');
    await countUps.first().scrollIntoViewIfNeeded();
    await page.waitForTimeout(1400);
    const countDisplays = await countUps.evaluateAll((nodes) => nodes.map((node) => node.textContent.trim()));
    if (JSON.stringify(countDisplays) !== JSON.stringify(['912+', '91%', '2.3', '1 in 2'])) throw new Error(`Home count-up values are wrong at ${viewport.width}: ${JSON.stringify(countDisplays)}`);
    if (viewport.width > 768) {
      if (await page.locator('.site-header.tbt-landing-header .site-brand__wordmark').count() !== 1 || await page.locator('.site-header.tbt-landing-header .tbt-landing-header__cta').count() !== 1) throw new Error(`Home must use the Inquire landing header at ${viewport.width}`);
      if (await page.locator('.site-header .site-nav').count() !== 0) throw new Error('Home landing header must not render the navigation menu');
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
      if (!contactButton || contactButton.y <= contactHeading.y + contactHeading.height || Math.abs(contactButton.width - 328) > 1 || Math.abs(contactButton.height - 69) > 1) throw new Error(`Contact button geometry is wrong: ${JSON.stringify(contactButton)}`);

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
  await resizePage.goto(`${base}/classes/`, { waitUntil: 'load' });
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
