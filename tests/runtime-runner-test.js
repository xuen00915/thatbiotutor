const { test } = require('node:test');
const assert = require('node:assert/strict');
const { runTests, suites, expandSuites } = require('../scripts/run-browser-tests');

test('keeps every existing exported-site suite and excludes mutable admin tests', () => {
  assert.equal(suites.length, 32);
  assert.equal(new Set(suites).size, 32);
  assert.ok(suites.includes('runtime-browser-test.js'));
  assert.ok(suites.includes('site-feedback-runtime.js'));
  assert.ok(!suites.includes('admin-editing-runtime.js'));
});

test('bounds concurrency, runs every test once, and reports failures', async () => {
  let active = 0, peak = 0;
  const seen = [];
  const results = await runTests(['slow', 'failed', 'last', 'extra'], 2, async name => {
    active++; peak = Math.max(peak, active); seen.push(name);
    await new Promise(resolve => setTimeout(resolve, name === 'slow' ? 30 : 5));
    active--;
    if(name === 'failed') throw new Error('fixture failure');
    return { code: 0 };
  });
  assert.equal(peak, 2);
  assert.deepEqual(seen.sort(), ['extra', 'failed', 'last', 'slow']);
  assert.equal(results.filter(result => result.code !== 0).length, 1);
});

test('rejects invalid worker counts before launching any suites', async () => {
  for (const workers of [0, -1, NaN, 1.5, 9]) {
    await assert.rejects(runTests(['one'], workers, () => assert.fail('must not execute')));
  }
});

test('third-party isolation only matches YouTube embed URLs', () => {
  const { isYouTubeEmbed } = require('../scripts/browser-test-setup');
  assert.equal(isYouTubeEmbed('https://www.youtube-nocookie.com/embed/video'), true);
  assert.equal(isYouTubeEmbed('https://www.youtube.com/embed/video'), true);
  for(const url of ['http://localhost:4173/embed/video', 'https://www.youtube.com/watch?v=video', 'https://www.youtube.com.evil.example/embed/video']) assert.equal(isYouTubeEmbed(url), false);
});


test('splits the long feedback suite into four exhaustive groups without dropping other suites', () => {
  const expanded = expandSuites(suites);
  assert.equal(expanded.length, suites.length + 3);
  assert.deepEqual(expanded.filter(name => name.startsWith('site-feedback-runtime.js#')).sort(),
    ['content', 'layout', 'sections', 'years'].map(group => `site-feedback-runtime.js#${group}`).sort());
  assert.deepEqual(expanded.filter(name => !name.includes('#')), suites.filter(name => name !== 'site-feedback-runtime.js'));
});
