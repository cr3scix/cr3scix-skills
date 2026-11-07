// Opens every app in a real browser and fails on any page error.
// Run: php -S 127.0.0.1:8081 index.php & node tests/browser.js
const { chromium } = require('playwright');
const base = process.env.BASE || 'http://127.0.0.1:8081';
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1280, height: 900 } });
  const errs = [];
  p.on('pageerror', e => errs.push(e.message));
  p.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
  await p.goto(base + '/');
  const apps = await p.evaluate(() => JSON.parse(document.getElementById('data').textContent).apps.map(a => a.name));
  for (const n of apps) {
    const before = errs.length;
    await p.goto(base + '/#/app/' + n);
    await p.waitForSelector('#main .cols', { timeout: 8000 }).catch(() => errs.push(n + ': did not render'));
    await p.waitForTimeout(300);
    if (await p.$('#main .err[role=alert]')) errs.push(n + ': shows an error on load');
    console.log((errs.length > before ? 'FAIL ' : 'ok   ') + n);
  }
  for (const r of ['#/', '#/history', '#/connect', '#/skill/manual-work-estimator']) {
    await p.goto(base + '/' + r); await p.waitForTimeout(200);
  }
  await b.close();
  if (errs.length) { console.log(errs.join('\n')); process.exit(1); }
  console.log(apps.length + ' apps opened without errors');
})();
