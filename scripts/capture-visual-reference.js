'use strict';

const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

const projectRoot = path.resolve(__dirname, '..');
const defaultManifestPath = path.join(projectRoot, 'resources', 'visual-reference-manifest.json');

function validateManifest(value) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        throw new TypeError('Visual reference manifest must be an object.');
    }
    if (!value.routes || typeof value.routes !== 'object' || Array.isArray(value.routes)) {
        throw new TypeError('Visual reference manifest routes must be an object.');
    }
    if (!Array.isArray(value.viewports) || value.viewports.length === 0) {
        throw new TypeError('Visual reference manifest viewports must be a non-empty array.');
    }
    for (const [key, route] of Object.entries(value.routes)) {
        if (!key || !route || typeof route.path !== 'string' || !route.path.startsWith('/')) {
            throw new TypeError(`Visual reference route ${key || '(empty)'} must have an absolute path.`);
        }
    }
    for (const viewport of value.viewports) {
        if (!Number.isInteger(viewport.width) || viewport.width < 320 || !Number.isInteger(viewport.height) || viewport.height < 1) {
            throw new TypeError('Visual reference viewports require integer width >= 320 and positive integer height.');
        }
    }
}

function parseArgs(argv) {
    const raw = {};
    for (let index = 0; index < argv.length; index += 1) {
        const argument = argv[index];
        if (!argument.startsWith('--')) {
            continue;
        }
        const separator = argument.indexOf('=');
        const key = argument.slice(2, separator === -1 ? undefined : separator);
        const value = separator === -1 && argv[index + 1] && !argv[index + 1].startsWith('--')
            ? argv[++index]
            : separator === -1 ? true : argument.slice(separator + 1);
        raw[key] = value;
    }

    const requireUrl = (key, label) => {
        if (!raw[key]) {
            throw new TypeError(`${label} is required.`);
        }
        try {
            return new URL(String(raw[key])).toString().replace(/\/$/, '');
        } catch (error) {
            throw new TypeError(`${label} must be a valid URL.`);
        }
    };

    if (!raw.output) {
        throw new TypeError('Output directory is required.');
    }

    return {
        referenceBase: requireUrl('reference-base', 'Reference base URL'),
        localBase: requireUrl('local-base', 'Local base URL'),
        output: path.resolve(String(raw.output)),
        route: raw.route ? String(raw.route) : null,
        freeze: Boolean(raw.freeze),
        navigationTimeout: raw['navigation-timeout'] ? Number(raw['navigation-timeout']) : 60000,
    };
}

async function freezePage(page) {
    await page.evaluate(() => {
        document.documentElement.setAttribute('data-tbt-visual-capture', 'frozen');
        for (const media of document.querySelectorAll('video, audio')) {
            media.pause();
        }
    });
    await page.addStyleTag({ content: `
        html[data-tbt-visual-capture="frozen"] *,
        html[data-tbt-visual-capture="frozen"] *::before,
        html[data-tbt-visual-capture="frozen"] *::after {
            animation: none !important;
            caret-color: transparent !important;
            scroll-behavior: auto !important;
            transition: none !important;
        }
    ` });
}

function joinUrl(base, routePath) {
    return new URL(routePath.replace(/^\//, ''), `${base}/`).toString();
}

async function collectMetrics(page, side, routeKey, viewport, url) {
    return page.evaluate(({ currentSide, currentRoute, currentViewport, currentUrl }) => {
        const box = (element) => {
            if (!element) return null;
            const rect = element.getBoundingClientRect();
            return {
                x: Math.round(rect.x),
                y: Math.round(rect.y),
                width: Math.round(rect.width),
                height: Math.round(rect.height),
            };
        };
        return {
            side: currentSide,
            route: currentRoute,
            viewport: currentViewport,
            url: currentUrl,
            title: document.title,
            document: {
                width: document.documentElement.scrollWidth,
                height: document.documentElement.scrollHeight,
                clientWidth: document.documentElement.clientWidth,
                overflowX: document.documentElement.scrollWidth > document.documentElement.clientWidth,
            },
            bodyFont: getComputedStyle(document.body).fontFamily,
            header: box(document.querySelector('[data-site-header], header')),
            sections: Array.from(document.querySelectorAll('main section, main header')).map((element, index) => ({
                index,
                key: element.getAttribute('data-section') || element.id || element.className || element.tagName.toLowerCase(),
                box: box(element),
            })),
        };
    }, { currentSide: side, currentRoute: routeKey, currentViewport: viewport.width, currentUrl: url });
}

async function captureMatrix(options) {
    const manifestPath = options.manifestPath || defaultManifestPath;
    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    validateManifest(manifest);

    if (options.route && !manifest.routes[options.route]) {
        throw new RangeError(`Unknown visual reference route: ${options.route}`);
    }

    const routes = options.route ? [[options.route, manifest.routes[options.route]]] : Object.entries(manifest.routes);
    const outputRoot = path.resolve(options.output);
    fs.mkdirSync(outputRoot, { recursive: true });
    const browser = await chromium.launch({ headless: true });
    const metrics = [];

    try {
        for (const [side, base] of [['reference', options.referenceBase], ['local', options.localBase]]) {
            for (const [routeKey, route] of routes) {
                for (const viewport of manifest.viewports) {
                    const url = joinUrl(base, route.path);
                    const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1 });
                    try {
                        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: options.navigationTimeout || 60000 });
                        await page.waitForTimeout(750);
                        if (options.freeze) {
                            await freezePage(page);
                        }
                        const routeDirectory = path.join(outputRoot, side, routeKey);
                        fs.mkdirSync(routeDirectory, { recursive: true });
                        await page.screenshot({ path: path.join(routeDirectory, `${viewport.width}.png`), fullPage: true, animations: 'disabled' });
                        metrics.push(await collectMetrics(page, side, routeKey, viewport, url));
                    } catch (error) {
                        throw new Error(`Visual capture failed for ${routeKey} at ${url}: ${error.message}`);
                    } finally {
                        await page.close();
                    }
                }
            }
        }
    } finally {
        await browser.close();
    }

    fs.writeFileSync(path.join(outputRoot, 'metrics.json'), `${JSON.stringify(metrics, null, 2)}\n`);
    const { buildVisualReview } = require('./build-visual-review.js');
    buildVisualReview(outputRoot, manifest);
}

module.exports = { captureMatrix, freezePage, parseArgs, validateManifest };

if (require.main === module) {
    let options;
    try {
        options = parseArgs(process.argv.slice(2));
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
    if (options) {
        captureMatrix(options).catch((error) => {
            console.error(error.message);
            process.exitCode = 1;
        });
    }
}
