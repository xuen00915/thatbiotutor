// Loaded only by the layout-test runner. No production code uses this file.
// Keep iframe src/title/dimensions intact while avoiding YouTube network variability.
const { chromium } = require('playwright');
const contexts = new WeakSet();
function isYouTubeEmbed(value) {
  const url = new URL(value);
  return ['www.youtube.com', 'www.youtube-nocookie.com'].includes(url.hostname) && url.pathname.startsWith('/embed/');
}
async function prepareContext(context) {
  if (contexts.has(context) || process.env.TBT_LIVE_EMBEDS === '1') return;
  contexts.add(context);
  await context.route(url => isYouTubeEmbed(url.href) || url.href === 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/', route => {
    if (route.request().resourceType() !== 'document') return route.continue();
    return route.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><title>Third-party embed layout fixture</title>' });
  });
}
const launch = chromium.launch.bind(chromium);
chromium.launch = async (...args) => {
  const browser = await launch(...args);
  const newPage = browser.newPage.bind(browser);
  const newContext = browser.newContext.bind(browser);
  browser.newContext = async (...options) => {
    const context = await newContext(...options);
    await prepareContext(context);
    return context;
  };
  browser.newPage = async (...options) => {
    const page = await newPage(...options);
    await prepareContext(page.context());
    return page;
  };
  return browser;
};
module.exports = { isYouTubeEmbed };
