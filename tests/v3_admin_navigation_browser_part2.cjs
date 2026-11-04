/* Live HTTP browser smoke for the V3 Part 2 admin sidebar.
 * Test against a local disposable *_test database/server ONLY. Requires
 * NAV_TEST_URL, NAV_TEST_LOGIN_EMAIL and NAV_TEST_LOGIN_PASSWORD.
 */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const base = process.env.NAV_TEST_URL;
const email = process.env.NAV_TEST_LOGIN_EMAIL;
const password = process.env.NAV_TEST_LOGIN_PASSWORD;
if (!base || !email || !password || !/^http:\/\/(?:127\.0\.0\.1|localhost):\d+$/.test(base)) {
  throw new Error('Set NAV_TEST_URL to local HTTP test server and supply disposable test login variables.');
}
(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  let checks = 0;
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto(base + '/admin');
    assert.equal(new URL(page.url()).pathname, '/login'); checks++;
    await page.locator('input[name=email]').first().fill(email);
    await page.locator('input[name=password]').fill(password);
    await Promise.all([page.waitForNavigation(), page.locator('form[action$="login"] button[type=submit]').click()]);
    assert.equal(new URL(page.url()).pathname, '/admin'); checks++;
    const paths = await page.locator('nav.dash-nav a[href]').evaluateAll(links =>
      [...new Set(links.map(a => new URL(a.href).pathname).filter(path => path.startsWith('/admin')))]);
    assert(paths.length >= 35, `Only ${paths.length} admin menu/submenu entries`); checks++;
    for (const path of paths) {
      const response = await page.goto(base + path);
      assert.equal(response.status(), 200, path + ' loads');
      assert.equal(await page.locator('.dash-main').count(), 1, path + ' shows admin shell');
      const active = await page.locator('nav.dash-nav a[aria-current="page"]').evaluateAll(a => a.map(el => new URL(el.href).pathname));
      assert.deepEqual(active, [path], path + ' has correct active link');
      const refreshed = await page.reload();
      assert.equal(refreshed.status(), 200, path + ' survives real browser refresh');
      checks += 4;
    }
    for (const width of [1024, 820, 390, 320]) {
      await page.setViewportSize({ width, height: 800 });
      await page.goto(base + '/admin/reports/donations');
      assert((await page.locator('.nav-details').getAttribute('open')) !== null, 'nested reports menu opens on active report');
      assert.equal(await page.locator('nav.dash-nav a[aria-current="page"]').count(), 1);
      await page.locator('[data-side-toggle]').click();
      assert.equal(await page.locator('[data-side-toggle]').getAttribute('aria-expanded'), 'true');
      await page.locator('#adminSide').waitFor({ state: 'visible' });
      await page.keyboard.press('Escape');
      assert.equal(await page.locator('[data-side-toggle]').getAttribute('aria-expanded'), 'false');
      await page.locator('[data-side-toggle]').click();
      await page.locator('.nav-details summary').click();
      assert((await page.locator('.nav-details').getAttribute('open')) === null, 'nested submenu closes');
      await page.locator('.nav-details summary').click();
      assert((await page.locator('.nav-details').getAttribute('open')) !== null, 'nested submenu reopens');
      await Promise.all([page.waitForNavigation(), page.locator('nav.dash-nav a[href$="/admin/reports/financial"]').click()]);
      assert.equal(new URL(page.url()).pathname, '/admin/reports/financial');
      assert.equal((await page.reload()).status(), 200, 'submenu target refreshes');
      const widthNow = await page.evaluate(() => document.documentElement.scrollWidth);
      assert(widthNow <= width + 2, `horizontal overflow at ${width}: ${widthNow}`);
      checks += 11;
    }
    await page.close();
  } finally { await browser.close(); }
  console.log(`${checks} V3 Part 2 live browser navigation checks passed.`);
})().catch(e => { console.error(e); process.exitCode = 1; });
