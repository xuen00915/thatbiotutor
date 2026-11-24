const { test } = require('node:test');
const assert = require('node:assert/strict');
const http = require('node:http');
const { runSmoke } = require('../scripts/preview-smoke');

test('preview gate accepts a working page and rejects blank, overflowing and missing-asset pages', async () => {
  const server = http.createServer((req, res) => {
    if (req.url === '/bad.js') { res.setHeader('Content-Type', 'application/javascript'); return res.end('throw new Error("Broken preview script")'); }
    if (req.url === '/missing.css') { res.writeHead(404); return res.end(); }
    const shell = content => `<html><head>${req.url === '/asset/' ? '<link rel="stylesheet" href="/missing.css">' : req.url === '/error/' ? '<script src="/bad.js"></script>' : ''}<style>body{margin:0}header,footer{height:50px}</style></head><body><header class="site-header">Header</header><main>${content}</main><footer class="site-footer">Footer</footer></body></html>`;
    res.setHeader('Content-Type', 'text/html');
    res.end(shell(req.url === '/blank/' ? '' : `<h1>Preview test</h1><p>A real page with visible content.</p>${req.url === '/wide/' ? '<div style="width:2000px;height:20px">Too wide</div>' : ''}`));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const base = `http://127.0.0.1:${server.address().port}/`;
  try {
    await runSmoke({ base, routes: [''], widths: [390] });
    for (const route of ['blank/', 'wide/', 'asset/', 'error/']) {
      await assert.rejects(runSmoke({ base, routes: [route], widths: [390] }), undefined, `${route} must block publication`);
    }
  } finally { await new Promise(resolve => server.close(resolve)); }
});
