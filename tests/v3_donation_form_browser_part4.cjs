/* Live browser smoke against a disposable *_test database. Never point at production. */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const base = process.env.DONATION_TEST_URL;
if (!base || !/^http:\/\/(?:127\.0\.0\.1|localhost):\d+$/.test(base)) throw new Error('Set DONATION_TEST_URL to a local disposable test server.');
(async () => {
  const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
  let checks = 0;
  try {
    for (const width of [1440, 390, 320]) {
      const page = await browser.newPage({viewport:{width,height:840}});
      const errors = [];
      page.on('pageerror', err => errors.push(err.message));
      const response = await page.goto(base+'/donate');
      assert.equal(response.status(), 200); checks++;
      assert.equal(await page.locator('#donateForm input[name=name]').isVisible(), true); checks++;
      assert.equal(await page.locator('#donateForm input[name=email]').isVisible(), true); checks++;
      assert.equal(await page.locator('details.donate-extra').last().getAttribute('open'), null); checks++;
      assert.equal(await page.locator('[data-online-method]:visible').count(), 0, 'no configured gateway advertised'); checks++;
      assert.equal(await page.locator('#bankMethod').isVisible(), true); checks++;
      await page.locator('#donationCurrency').selectOption('USD');
      assert.equal(await page.locator('#sumCurrency').textContent(), 'USD'); checks++;
      assert(await page.locator('[data-bank-currency="USD"]:visible').count() > 0); checks++;
      assert.equal(await page.locator('[data-bank-currency="PKR"]:visible').count(), 0); checks++;
      assert.match(await page.locator('#reportTransfer').getAttribute('href'), /currency=USD/); checks++;
      await page.locator('#donateSubmit').click();
      assert.match(await page.locator('#donateFeedback').textContent(), /not complete a donation/); checks++;
      assert.equal(new URL(page.url()).pathname, '/donate', 'bank choice never creates online payment'); checks++;
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
      assert(overflow <= 2, `${width}px viewport has ${overflow}px horizontal overflow`); checks++;
      assert.deepEqual(errors, [], 'no JS runtime errors'); checks++;
      await page.close();
    }
    const page = await browser.newPage({viewport:{width:390,height:844}});
    await page.goto(base+'/donate/transfer?currency=USD');
    assert.equal(await page.locator('#transferCurrency').inputValue(), 'USD'); checks++;
    await page.locator('[name=donor_name]').fill('Browser Guest');
    await page.locator('[name=donor_email]').fill('browser4@example.org');
    await page.locator('[name=donor_phone]').fill('03001234567');
    await page.locator('[name=amount]').fill('13.75');
    await page.locator('[name=received_at]').fill(new Date().toISOString().slice(0,10));
    const originalToken = await page.locator('[name=transfer_attempt]').inputValue();
    await Promise.all([page.waitForNavigation(), page.getByRole('button',{name:'Submit for review'}).click()]);
    assert.match(await page.locator('[role=status]').first().textContent(), /submitted/); checks++;
    const csrf = await page.locator('form[action$="donate/transfer"] [name=_token]').inputValue();
    const replay = await page.evaluate(async ({csrf,originalToken}) => {
      const data = new URLSearchParams({_token:csrf,transfer_attempt:originalToken,donor_name:'Browser Guest',donor_email:'browser4@example.org',donor_phone:'03001234567',amount:'13.75',currency:'USD',received_at:new Date().toISOString().slice(0,10),source:'bank_transfer'});
      const result = await fetch('/donate/transfer',{method:'POST',body:data,headers:{'Content-Type':'application/x-www-form-urlencoded'}});
      return {status:result.status,body:await result.text()};
    }, {csrf,originalToken});
    assert.equal(replay.status, 200); checks++;
    assert.match(replay.body, /already sent or has expired/); checks++;
    await page.close();
  } finally { await browser.close(); }
  console.log(`${checks} V3 Part 4 live browser donation checks passed.`);
})().catch(err => {console.error(err);process.exitCode=1;});
