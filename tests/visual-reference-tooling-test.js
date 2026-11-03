'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const projectRoot = path.resolve(__dirname, '..');
const manifestPath = path.join(projectRoot, 'resources', 'visual-reference-manifest.json');
const capturePath = path.join(projectRoot, 'scripts', 'capture-visual-reference.js');
const reviewPath = path.join(projectRoot, 'scripts', 'build-visual-review.js');

async function main() {
    assert.ok(fs.existsSync(manifestPath), 'visual reference manifest must exist');
    assert.ok(fs.existsSync(capturePath), 'capture utility must exist');
    assert.ok(fs.existsSync(reviewPath), 'review utility must exist');

    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    const capture = require(capturePath);
    const review = require(reviewPath);

    capture.validateManifest(manifest);
    assert.deepEqual(Object.keys(manifest.routes), [
        'home', 'classes', 'sec3', 'sec4', 'testimonials', 'notes',
        'celldivision', 'about', 'contact', 'trees', 'tuitionrecs',
    ]);
    assert.deepEqual(manifest.viewports.map((viewport) => viewport.width), [1440, 768, 390, 320]);

    assert.throws(
        () => capture.parseArgs(['--reference-base=not-a-url', '--local-base=http://localhost:8888', '--output=/tmp/output']),
        /reference base.*valid URL/i,
        'invalid reference base must be rejected before opening a browser',
    );
    assert.throws(
        () => capture.parseArgs(['--reference-base=https://www.thatbiotutor.com', '--output=/tmp/output']),
        /local base.*required/i,
        'missing local base must be rejected clearly',
    );

    const { chromium } = require('playwright');
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.setContent('<div data-carousel><div style="animation: pulse 2s infinite; transition: all 1s">Test</div></div>');
    await capture.freezePage(page);
    const frozen = await page.evaluate(() => ({
        attribute: document.documentElement.getAttribute('data-tbt-visual-capture'),
        animation: getComputedStyle(document.querySelector('[data-carousel] > div')).animationName,
        transition: getComputedStyle(document.querySelector('[data-carousel] > div')).transitionDuration,
    }));
    await browser.close();
    assert.equal(frozen.attribute, 'frozen');
    assert.equal(frozen.animation, 'none');
    assert.equal(frozen.transition, '0s');

    const tempRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'tbt-visual-test-'));
    const runRoot = path.join(tempRoot, 'run');
    for (const side of ['reference', 'local']) {
        for (const [routeKey] of Object.entries(manifest.routes)) {
            const routeDir = path.join(runRoot, side, routeKey);
            fs.mkdirSync(routeDir, { recursive: true });
            for (const viewport of manifest.viewports) {
                fs.writeFileSync(path.join(routeDir, `${viewport.width}.png`), 'fixture');
            }
        }
    }
    const reviewFile = review.buildVisualReview(runRoot, manifest);
    const html = fs.readFileSync(reviewFile, 'utf8');
    assert.match(html, /reference\/home\/1440\.png/);
    assert.match(html, /local\/tuitionrecs\/320\.png/);
    assert.equal((html.match(/class="comparison"/g) || []).length, 44);

    await assert.rejects(
        capture.captureMatrix({
            referenceBase: 'http://127.0.0.1:1',
            localBase: 'http://127.0.0.1:1',
            output: path.join(tempRoot, 'failed'),
            route: 'home',
            freeze: true,
            navigationTimeout: 750,
        }),
        /home.*http:\/\/127\.0\.0\.1:1/i,
        'navigation failure must name the route and URL',
    );

    fs.rmSync(tempRoot, { recursive: true, force: true });
    console.log('PASS: visual reference capture and review tooling');
}

main().catch((error) => {
    console.error(`FAIL: ${error.stack || error.message}`);
    process.exitCode = 1;
});
