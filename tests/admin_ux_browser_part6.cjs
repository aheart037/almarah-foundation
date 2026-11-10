/* Optional Chromium smoke checks against DB-rendered, self-contained admin snapshots.
   Run after: ADMIN_PREVIEW_DIR=$PWD/tests php tests/admin_ux_part6_test.php
   Install locally: npm install --no-save playwright && npx playwright install chromium
   The PHP test's snapshots use the disposable *_test database. Do not commit them. */
const { chromium } = require('playwright');
const path = require('path');
const { pathToFileURL } = require('url');

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  let checks = 0;
  try {
    for (const route of ['admin', 'admin-donations', 'admin-reports', 'admin-inbox-contact', 'admin-campaigns', 'admin-settings']) {
      for (const width of [1440, 1200, 820, 390, 320]) {
        const page = await browser.newPage({ viewport: { width, height: 850 } });
        try {
          await page.goto(pathToFileURL(path.join(__dirname, `.admin-${route}.html`)).href);
          const measure = await page.evaluate(() => ({ documentWidth: document.documentElement.scrollWidth, viewportWidth: innerWidth }));
          if (measure.documentWidth > width + 2) throw new Error(`${route} at ${width}px overflows the page: ${JSON.stringify(measure)}`);
          checks++;
          if (width <= 1024) {
            await page.locator('[data-side-toggle]').click();
            await page.locator('.nav-details').waitFor({ state: 'visible' });
            if (await page.locator('[data-side-toggle]').getAttribute('aria-expanded') !== 'true') throw new Error(`${route} drawer did not open`);
            await page.keyboard.press('Escape');
            if (await page.locator('[data-side-toggle]').getAttribute('aria-expanded') !== 'false') throw new Error(`${route} drawer did not close`);
            checks++;
          }
          if (route === 'admin') {
            await page.locator('#recentSearch').fill('no matching reference');
            if (!await page.locator('[data-search-empty="recentTransactions"]').isVisible()) throw new Error('Transaction search missing empty state');
            await page.locator('#recentSearch').fill('');
            if (await page.locator('#recentTransactions tbody tr:visible').count() === 0) throw new Error('Transaction search did not restore rows');
            checks++;
          }
          if (route === 'admin-reports' && width === 390) {
            await page.locator('[data-side-toggle]').click();
            if ((await page.locator('.nav-details').getAttribute('open')) === null) throw new Error('Active Reports menu not expanded');
            checks++;
          }
        } finally { await page.close(); }
      }
    }
  } finally { await browser.close(); }
  console.log(`${checks} Chromium admin layout and interaction checks passed.`);
})().catch(error => { console.error(error); process.exitCode = 1; });
