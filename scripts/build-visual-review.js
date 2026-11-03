'use strict';

const fs = require('node:fs');
const path = require('node:path');

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    })[character]);
}

function buildVisualReview(runRoot, manifest) {
    const comparisons = [];
    for (const [routeKey] of Object.entries(manifest.routes)) {
        for (const viewport of manifest.viewports) {
            const reference = `reference/${routeKey}/${viewport.width}.png`;
            const local = `local/${routeKey}/${viewport.width}.png`;
            comparisons.push(`
                <section class="comparison" data-route="${escapeHtml(routeKey)}" data-viewport="${viewport.width}">
                    <h2>${escapeHtml(routeKey)} · ${viewport.width}px</h2>
                    <label>Overlay opacity <input type="range" min="0" max="100" value="50" data-opacity></label>
                    <div class="views">
                        <figure><figcaption>Reference</figcaption><img src="${reference}" alt="Reference ${escapeHtml(routeKey)} at ${viewport.width}px"></figure>
                        <figure><figcaption>Local</figcaption><img src="${local}" alt="Local ${escapeHtml(routeKey)} at ${viewport.width}px"></figure>
                    </div>
                    <div class="overlay"><img src="${reference}" alt=""><img class="local" src="${local}" alt=""></div>
                </section>`);
        }
    }

    const html = `<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ThatBioTutor visual review</title>
<style>
body{margin:0;padding:24px;font:16px/1.5 system-ui;background:#161916;color:#fff}.comparison{margin:0 auto 64px;max-width:1600px}.views{display:grid;grid-template-columns:1fr 1fr;gap:16px}figure{margin:0}figcaption{margin-bottom:6px}.views img,.overlay img{display:block;width:100%;height:auto}.overlay{position:relative;margin-top:16px;overflow:hidden;background:#fff}.overlay .local{position:absolute;inset:0;opacity:.5}@media(max-width:800px){.views{grid-template-columns:1fr}}
</style></head><body><h1>ThatBioTutor visual review</h1>${comparisons.join('')}
<script>document.querySelectorAll('.comparison').forEach(section=>{const slider=section.querySelector('[data-opacity]');const local=section.querySelector('.overlay .local');slider.addEventListener('input',()=>{local.style.opacity=String(Number(slider.value)/100);});});</script>
</body></html>`;
    const outputPath = path.join(runRoot, 'review.html');
    fs.writeFileSync(outputPath, html);
    return outputPath;
}

module.exports = { buildVisualReview };

if (require.main === module) {
    const runRoot = process.argv[2] ? path.resolve(process.argv[2]) : null;
    if (!runRoot) {
        console.error('Usage: node scripts/build-visual-review.js <capture-directory>');
        process.exitCode = 1;
    } else {
        const manifest = JSON.parse(fs.readFileSync(path.resolve(__dirname, '..', 'resources', 'visual-reference-manifest.json'), 'utf8'));
        console.log(buildVisualReview(runRoot, manifest));
    }
}
