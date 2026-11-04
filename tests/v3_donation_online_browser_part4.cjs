/* Configured-GATEWAY UI test with unreachable local mock endpoint, disposable DB only. */
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const base=process.env.DONATION_TEST_URL;
if (!base||!/^http:\/\/(?:127\.0\.0\.1|localhost):\d+$/.test(base)) throw Error('Use a local disposable test server');
(async()=>{
 const browser=await chromium.launch({headless:true,args:['--no-sandbox']});let checks=0;
 try{
  const page=await browser.newPage({viewport:{width:390,height:840}});
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'/donate');
  assert.equal(await page.locator('[data-online-method]:visible').count(),1);checks++;
  assert.equal(await page.locator('input[name=gateway]:checked').inputValue(),'meezan');checks++;
  await page.locator('#customAmount').fill('1');
  assert.match(await page.locator('#sumAmount').textContent(),/PKR 1/);checks++;
  await page.locator('#donateSubmit').click();
  assert.match(await page.locator('#donateFeedback').textContent(),/Enter an amount between/);checks++;
  assert.equal(await page.locator('#donateSubmit').isDisabled(),false);checks++;
  await page.locator('#customAmount').fill('200');
  await page.locator('#donateSubmit').click();
  assert.equal(await page.locator('input[name=name]').evaluate(e=>e.matches(':invalid')),true,'required donor name');checks++;
  assert.equal(await page.locator('#donateSubmit').isDisabled(),false);checks++;
  await page.locator('input[name=name]').fill('Online Test Guest');
  await page.locator('#donateForm input[name=email]').fill('bad-email');
  await page.locator('#donateSubmit').click();
  assert.equal(await page.locator('#donateForm input[name=email]').evaluate(e=>e.matches(':invalid')),true,'email checked');checks++;
  const token=await page.locator('#donateForm [name=donation_attempt]').inputValue();
  const csrf=await page.locator('#donateForm [name=_token]').inputValue();
  // A forged USD-online POST must be rejected before any payment registration.
  const reject=await page.evaluate(async ({token,csrf})=>{
   const result=await fetch('/fundraise-submit.php',{method:'POST',body:new URLSearchParams({_token:csrf,donation_attempt:token,type:'purpose',target:'general-fund',name:'Online Test Guest',email:'online4@example.org',amount:'200',currency:'USD',gateway:'meezan'}),headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}});
   return await result.json();
  },{token,csrf});
  assert.equal(reject.success,false);assert.match(reject.message,/PKR only/);checks+=2;
  await page.locator('#donateForm input[name=email]').fill('online4@example.org');
  await Promise.all([page.waitForNavigation(),page.locator('#donateSubmit').click()]);
  assert.equal(new URL(page.url()).pathname,'/donate','unreachable test gateway fails back to form');checks++;
  assert.match(await page.locator('[role=alert]').first().textContent(),/gateway|payment|connection|unavailable|registration|could not/i);checks++;
  // The second POST uses the consumed token, so cannot create another donation.
  const replay=await page.evaluate(async ({token,csrf})=>{
   const result=await fetch('/fundraise-submit.php',{method:'POST',body:new URLSearchParams({_token:csrf,donation_attempt:token,type:'purpose',target:'general-fund',name:'Online Test Guest',email:'online4@example.org',amount:'200',currency:'PKR',gateway:'meezan'}),headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}});
   return await result.json();
  },{token,csrf});
  assert.equal(replay.success,false);assert.match(replay.message,/already sent/);checks+=2;
  assert.deepEqual(errors,[]);checks++;
  await page.close();
 } finally {await browser.close()}
 console.log(`${checks} V3 Part 4 configured-gateway browser checks passed.`);
})().catch(err=>{console.error(err);process.exitCode=1;});
