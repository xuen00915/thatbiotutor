const { spawn } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');

// Static-export tests are isolated browser processes; mutable wp-admin checks run separately.
const suites = [
  "tablet-heroes-runtime.js",
  "site-feedback-runtime.js",
  "year-navigation-runtime.js",
  "mobile-tablet-runtime.js",
  "runtime-browser-test.js",
  "content-width-runtime.js",
  "home-polish-runtime.js",
  "intensivefix-button-runtime.js",
  "standard-pages-runtime.js",
  "content-length-runtime.js",
  "causes-page-runtime.js",
  "about-page-runtime.js",
  "classes-page-runtime.js",
  "screenshot-feedback-runtime.js",
  "carousel-motion-runtime.js",
  "tick-alignment-runtime.js",
  "sec3-page-runtime.js",
  "sec3-refinements-runtime.js",
  "sec4-page-runtime.js",
  "programme-polish-runtime.js",
  "site-polish-runtime.js",
  "nav-arrow-runtime.js",
  "notes-hero-runtime.js",
  "notes-sections-runtime.js",
  "notes-form-runtime.js",
  "review-panel-runtime.js",
  "testimonials-page-runtime.js",
  "header-definition-runtime.js",
  "seo-metadata-runtime.js",
  "unified-design-runtime.js",
  "feedback-nov23-runtime.js"
];

async function runTests(names, workers, execute) {
  if (!Number.isInteger(workers) || workers < 1 || workers > 8) throw new Error('Worker count must be an integer from 1 to 8');
  let cursor = 0;
  const results = [];
  await Promise.all(Array.from({ length: Math.min(workers, names.length) }, async () => {
    while (cursor < names.length) {
      const name = names[cursor++];
      const start = Date.now();
      let result;
      try { result = await execute(name); }
      catch (error) { result = { code: 1, error: error.message }; }
      results.push({ name, seconds: (Date.now() - start) / 1000, ...result });
    }
  }));
  return results;
}

function expandSuites(names) {
  return names.flatMap(name => name === 'site-feedback-runtime.js'
    ? ['sections', 'layout', 'years', 'content'].map(group => `${name}#${group}`)
    : [name]);
}

async function main() {
  const requested = process.argv.slice(2);
  const names = requested.length ? requested : suites;
  if (names.some(name => !suites.includes(name))) throw new Error('Unknown suite; use a filename listed in run-browser-tests.js');
  const workers = Number(process.env.TBT_RUNTIME_WORKERS || 3);
  const logDir = path.resolve('.artifacts/browser-tests');
  fs.mkdirSync(logDir, { recursive: true });
  const started = Date.now();
  const results = await runTests(expandSuites(names), workers, name => new Promise(resolve => {
    console.log(`START ${name}`);
    const log = fs.createWriteStream(path.join(logDir, `${name}.log`));
    const [file, group] = name.split('#');
    const child = spawn(process.execPath, ['--require', './scripts/browser-test-setup.js', path.join('tests', file), ...(group ? [group] : [])], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
    child.stdout.pipe(log, { end: false });
    child.stderr.pipe(log, { end: false });
    let spawnError;
    child.on('error', error => { spawnError = error.message; log.write(spawnError); });
    child.on('close', code => log.end(() => {
      console.log(`${code === 0 ? 'PASS' : 'FAIL'} ${name}`);
      if (code !== 0) console.error(fs.readFileSync(path.join(logDir, `${name}.log`), 'utf8'));
      resolve({ code: code === 0 ? 0 : 1, ...(spawnError ? { error: spawnError } : {}) });
    }));
  }));
  results.sort((a, b) => b.seconds - a.seconds);
  const report = { workers, seconds: (Date.now() - started) / 1000, results };
  fs.writeFileSync(path.join(logDir, 'timings.json'), JSON.stringify(report, null, 2));
  const summary = `## Browser verification\n\n${results.length} suites, ${workers} workers, ${report.seconds.toFixed(1)} seconds.\n\n| Suite | Seconds | Result |\n|---|---:|---|\n` + results.map(r => `| ${r.name} | ${r.seconds.toFixed(1)} | ${r.code === 0 ? 'PASS' : 'FAIL'} |`).join('\n') + '\n';
  console.log(summary);
  if (process.env.GITHUB_STEP_SUMMARY) fs.appendFileSync(process.env.GITHUB_STEP_SUMMARY, summary);
  if (results.some(r => r.code !== 0)) process.exitCode = 1;
}

module.exports = { runTests, suites, expandSuites };
if (require.main === module) main().catch(error => { console.error(error); process.exitCode = 1; });
