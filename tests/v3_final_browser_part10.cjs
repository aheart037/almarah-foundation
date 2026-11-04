/* Live HTTP V3 QA: disposable local *_test DB only; credentials via environment. */
const {chromium} = require('playwright');
const assert = require('node:assert/strict');
const base = process.env.QA_TEST_URL;
if (!base || !/^http:\/\/(?:127\.0\.0\.1|localhost):\d+$/.test(base)) throw Error('QA_TEST_URL must be local disposable HTTP test server');
if (!process.env.QA_TEST_PASSWORD) throw Error('Supply disposable QA_TEST_PASSWORD');
(async () => {
  const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
  let checks=0;
  try {
    const login = async (page,email) => {
      await page.goto(base+'/login');
      await page.locator('input[name=email]').first().fill(email);
      await page.locator('input[name=password]').fill(process.env.QA_TEST_PASSWORD);
      await Promise.all([page.waitForNavigation(),page.locator('form[action$="login"] button[type=submit]').click()]);
      assert.equal(new URL(page.url()).pathname,'/admin');checks++;
    };
    const guest=await browser.newPage();
    await guest.goto(base+'/admin/grants');
    assert.equal(new URL(guest.url()).pathname,'/login');checks++;
    const csrfLess=await guest.request.post(base+'/donate/estimate',{data:{amount:'100',currency:'USD'}});
    assert.equal(csrfLess.status(),419);checks++;
    await guest.close();
    const viewer=await browser.newPage();
    await login(viewer,'qa-viewer@example.org');
    for(const path of ['/admin/grants','/admin/users','/admin/roles','/admin/reports/financial']) {
      const res=await viewer.goto(base+path);
      assert.equal(res.status(),403,`viewer denied ${path}`);checks++;
    }
    await viewer.close();
    const admin=await browser.newPage({viewport:{width:1440,height:850}});
    const errors=[];
    admin.on('pageerror',error=>errors.push(error.message));
    admin.on('response',res=>{if(res.url().startsWith(base+'/')&&res.status()>=500)errors.push(`${res.status()} ${res.url()}`)});
    await login(admin,'qa-super@example.org');
    for(const width of [1440,390,320]) {
      await admin.setViewportSize({width,height:850});
      for(const path of ['/admin/grants','/admin/grants/create','/admin/users','/admin/users/create','/admin/roles','/admin/roles/create','/admin/reports/donations','/admin/reports/financial']) {
        const res=await admin.goto(base+path);
        assert.equal(res.status(),200,`${path} at ${width}px`);checks++;
        assert.equal(await admin.locator('.dash-main').count(),1,`${path} admin shell`);checks++;
        const overflow=await admin.evaluate(()=>document.documentElement.scrollWidth-innerWidth);
        assert(overflow<=2,`${path} at ${width}px overflows by ${overflow}px`);checks++;
      }
    }
    assert.deepEqual(errors,[],'no JavaScript exceptions or same-origin 5xx responses');checks++;
    await admin.close();
  } finally {await browser.close()}
  console.log(`${checks} V3 Part 10 browser admin/permissions/mobile/API checks passed.`);
})().catch(error=>{console.error(error);process.exitCode=1});
