// End-to-end tests for privacy-policy-disclaimer.php on a real WordPress.
const { chromium } = require('playwright-core');
const { execSync } = require('child_process');
const BASE = process.env.PPD_BASE || 'http://127.0.0.1:8899';
const POST = BASE + '/?p=' + (process.env.PPD_POST_ID || 4);
const WP = (cmd) => execSync(`cd ${process.env.PPD_WP_PATH || './wp'} && ${process.env.PPD_WP_CLI || 'wp'} --allow-root ${cmd} 2>/dev/null`).toString().trim();
const setOpt = (obj) => {
  const cur = JSON.parse(WP('option get ppd_settings --format=json') || '{}');
  require('fs').writeFileSync('/tmp/ppd_opt.json', JSON.stringify(Object.assign(cur, obj)));
  WP('option update ppd_settings --format=json < /tmp/ppd_opt.json');
};
const DB = (sql, mode) => { const b = Buffer.from(sql).toString('base64'); return WP(`eval 'global $wpdb; $r=$wpdb->get_results(base64_decode("${b}"), ARRAY_N); foreach((array)$r as $row){echo implode("\t",$row),"\n";}'`); };
const logCount = () => parseInt(DB('SELECT COUNT(*) FROM wp_ppd_consent_log') || '0', 10);
let fails = 0, passes = 0;
const ok = (cond, name) => { if (cond) { passes++; console.log('  ok  ', name); } else { fails++; console.log('  FAIL', name); } };

async function page(browser, opts = {}) {
  const ctx = await browser.newContext(Object.assign({ viewport: { width: 1280, height: 800 } }, opts.ctx || {}));
  // Block external network (GTM, YouTube): the test is offline.
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => r.abort());
  if (opts.gpc) { await ctx.addInitScript(() => Object.defineProperty(Navigator.prototype, 'globalPrivacyControl', { get: () => true })); }
  const p = await ctx.newPage();
  const errors = [];
  p.on('pageerror', (e) => errors.push(e.message));
  p.errors = errors;
  return p;
}
const cookie = async (p) => { const c = (await p.context().cookies()).find((x) => x.name === 'ppd_consent'); return c ? JSON.parse(decodeURIComponent(c.value)) : null; };
const consentUpdates = (p) => p.evaluate(() => (window.dataLayer || []).filter((a) => a && a[0] === 'consent').map((a) => [a[1], a[2]]));

(async () => {
  // Make sure the log table exists (created on admin_init).
  WP("eval 'ppd_maybe_install();'");
  DB('DELETE FROM wp_ppd_consent_log');
  const browser = await chromium.launch({ executablePath: process.env.PPD_CHROMIUM || undefined });

  console.log('A. Opt-in, first visit');
  setOpt({ regime: 'optin', position: 'bar', embeds_block: 1 });
  let p = await page(browser);
  await p.goto(POST);
  ok(await p.isVisible('#ppd-banner'), 'banner visible');
  ok(!(await p.isVisible('#ppd-fab')), 'floating button hidden before choice');
  ok(await p.evaluate(() => !window.ppdTestAnalytics && !window.ppdTestMarketing), 'no optional script ran');
  ok((await p.locator('.ppd-embed').count()) === 2, 'two embeds replaced by placeholders');
  let cu = await consentUpdates(p);
  ok(cu[0] && cu[0][0] === 'default' && cu[0][1].analytics_storage === 'denied' && cu[0][1].ad_storage === 'denied', 'Consent Mode default denied');
  ok(await p.evaluate(() => document.querySelectorAll('h1,h2,h3').length === document.querySelectorAll('.wp-site-blocks h1,.wp-site-blocks h2,.wp-site-blocks h3, main h1, main h2').length || !document.querySelector('#ppd-root h1,#ppd-root h2,#ppd-root h3')), 'banner adds no headings');
  const accW = await p.locator('#ppd-banner [data-ppd-action="accept"]').evaluate((e) => getComputedStyle(e).backgroundColor);
  const rejW = await p.locator('#ppd-banner [data-ppd-action="reject"]').evaluate((e) => getComputedStyle(e).backgroundColor);
  ok(accW === rejW, 'accept and reject have the same style');
  await p.screenshot({ path: './shot-optin-bar.png' });

  console.log('B. Reject all');
  await p.click('#ppd-banner [data-ppd-action="reject"]');
  await p.waitForTimeout(400);
  let ck = await cookie(p);
  ok(ck && ck.c.analytics === 0 && ck.c.marketing === 0 && ck.c.preferences === 0 && ck.r === 'optin', 'cookie stores all denied');
  ok(!(await p.isVisible('#ppd-banner')) && (await p.isVisible('#ppd-fab')), 'banner hidden, floating button shown');
  ok(await p.evaluate(() => !window.ppdTestAnalytics && !window.ppdTestMarketing), 'still nothing ran');
  ok(logCount() === 1, 'log row written');

  console.log('C. Reload keeps choice; customize statistics only');
  await p.reload();
  ok(!(await p.isVisible('#ppd-banner')), 'banner not shown again');
  await p.click('#ppd-fab');
  ok(await p.isVisible('#ppd-prefs'), 'preferences dialog opens from floating button');
  ok(!(await p.isChecked('#ppd-cb-analytics')), 'switch reflects stored choice');
  await p.check('#ppd-cb-analytics');
  await p.click('#ppd-prefs [data-ppd-action="save"]');
  await p.waitForTimeout(400);
  ok(await p.evaluate(() => window.ppdTestAnalytics === 1 && !window.ppdTestMarketing), 'analytics code ran once, marketing not');
  cu = await consentUpdates(p);
  const last = cu[cu.length - 1][1];
  ok(last.analytics_storage === 'granted' && last.ad_storage === 'denied', 'Consent Mode update: analytics granted, ads denied');
  ok((await p.locator('.ppd-embed').count()) === 2, 'embeds still blocked');

  console.log('D. Embed: load once, then always');
  await p.click('.ppd-embed >> nth=0 >> [data-ppd-embed="load"]');
  ok((await p.locator('.ppd-embed').count()) === 1 && (await p.locator('iframe[src*="youtube.com/embed"]').count()) === 1, 'one embed loaded without saving consent');
  ck = await cookie(p);
  ok(ck.c.marketing === 0, 'load-once did not grant marketing');
  await p.click('.ppd-embed >> [data-ppd-embed="always"]');
  await p.waitForTimeout(400);
  ok((await p.locator('.ppd-embed').count()) === 0 && (await p.locator('iframe[src*="google.com/maps"]').count()) === 1, 'always allow loads remaining embed');
  ck = await cookie(p);
  ok(ck.c.marketing === 1, 'always allow granted marketing');
  ok(await p.evaluate(() => window.ppdTestMarketing === 1), 'enqueued marketing script ran after grant');

  console.log('E. Withdraw marketing: cookies cleared and page reloads');
  ok((await p.context().cookies()).some((c) => c.name === '_fbp'), '_fbp set by marketing script');
  await p.click('#ppd-fab');
  await p.uncheck('#ppd-cb-marketing');
  await Promise.all([p.waitForNavigation(), p.click('#ppd-prefs [data-ppd-action="save"]')]);
  ok(!(await p.context().cookies()).some((c) => c.name === '_fbp'), '_fbp removed');
  ok(await p.evaluate(() => !window.ppdTestMarketing && window.ppdTestAnalytics === 1), 'after reload only analytics runs');
  ok((await p.locator('.ppd-embed').count()) === 2, 'embeds blocked again');
  ok(p.errors.length === 0, 'no JS errors (' + p.errors.join(' | ') + ')');
  await p.context().close();

  console.log('F. Policy version bump asks again');
  p = await page(browser);
  await p.goto(POST);
  await p.click('#ppd-banner [data-ppd-action="accept"]');
  await p.waitForTimeout(300);
  ok(await p.evaluate(() => window.ppdTestAnalytics === 1 && window.ppdTestMarketing === 1), 'accept all runs everything');
  await p.reload();
  await p.waitForFunction(() => window.ppdTestMarketing === 1, null, { timeout: 3000 }).catch(() => {});
  ok(await p.evaluate(() => window.ppdTestAnalytics === 1 && window.ppdTestMarketing === 1 && !document.querySelector('.ppd-embed')), 'returning visitor: stored consent activates scripts printed after the banner code');
  setOpt({ policy_version: '2' });
  await p.reload();
  ok(await p.isVisible('#ppd-banner'), 'banner back after version change');
  ok(await p.evaluate(() => !window.ppdTestAnalytics), 'old consent not applied');
  await p.context().close();
  setOpt({ policy_version: '1' });

  console.log('G. Opt-out regime (CCPA/CPRA)');
  setOpt({ regime: 'optout' });
  p = await page(browser);
  await p.goto(POST);
  await p.waitForFunction(() => window.ppdTestMarketing === 1, null, { timeout: 3000 }).catch(() => {});
  ok(await p.isVisible('#ppd-banner [data-ppd-action="optout"]'), 'Do Not Sell button visible');
  ok(!(await p.isVisible('#ppd-banner [data-ppd-action="reject"]')), 'opt-in buttons hidden');
  ok(await p.evaluate(() => window.ppdTestAnalytics === 1 && window.ppdTestMarketing === 1), 'scripts run before choice (opt-out model)');
  cu = await consentUpdates(p);
  ok(cu[0][1].ad_storage === 'granted', 'Consent Mode default granted');
  await p.screenshot({ path: './shot-optout.png' });
  await Promise.all([p.waitForNavigation(), p.click('#ppd-banner [data-ppd-action="optout"]')]);
  ck = await cookie(p);
  ok(ck && ck.c.marketing === 0 && ck.c.analytics === 1 && ck.r === 'optout', 'opt-out stored: marketing off, analytics on');
  ok(await p.evaluate(() => !window.ppdTestMarketing && window.ppdTestAnalytics === 1), 'after reload marketing off');
  await p.context().close();

  console.log('H. Opt-out with Global Privacy Control');
  p = await page(browser, { gpc: true });
  await p.goto(POST);
  ok(await p.isVisible('#ppd-banner .ppd-gpc'), 'GPC notice shown');
  ok(await p.evaluate(() => !window.ppdTestMarketing && window.ppdTestAnalytics === 1), 'marketing never ran with GPC');
  cu = await consentUpdates(p);
  ok(cu[0][1].ad_storage === 'denied' && cu[0][1].analytics_storage === 'granted', 'Consent Mode default honors GPC');
  let navigated = false; p.on('framenavigated', (f) => { if (f === p.mainFrame()) { navigated = true; } });
  await p.click('#ppd-banner [data-ppd-action="acknowledge"]');
  await p.waitForTimeout(600);
  ck = await cookie(p);
  ok(ck.c.marketing === 0 && ck.g === 1, 'acknowledge stores marketing off with gpc flag');
  ok(!navigated, 'no reload needed');
  await p.context().close();

  console.log('I. Auto regime by country header');
  setOpt({ regime: 'auto' });
  for (const [cc, want] of [['US', 'optout'], ['BR', 'optin'], ['DE', 'optin'], ['JP', 'optin']]) {
    p = await page(browser, { ctx: { extraHTTPHeaders: { 'CF-IPCountry': cc } } });
    await p.goto(POST);
    await p.waitForSelector('#ppd-banner:not([hidden])');
    const vis = await p.isVisible('#ppd-banner [data-ppd-action="' + (want === 'optout' ? 'optout' : 'reject') + '"]');
    ok(vis, cc + ' -> ' + want);
    await p.context().close();
  }
  p = await page(browser, { ctx: { extraHTTPHeaders: { 'CF-IPCountry': 'US' } } });
  await p.goto(POST);
  ok(await p.evaluate(() => (window.dataLayer || []).find((a) => a && a[0] === 'consent')[2].ad_storage === 'denied'), 'auto: denied until regime is known');
  await p.context().close();

  console.log('J. Modal position');
  setOpt({ regime: 'optin', position: 'modal' });
  p = await page(browser);
  await p.goto(POST);
  ok(await p.evaluate(() => { const b = document.getElementById('ppd-banner'); return b.tagName === 'DIALOG' && b.open && b.matches(':modal'); }), 'banner is a modal dialog');
  await p.keyboard.press('Escape');
  ok(await p.isVisible('#ppd-banner'), 'Escape does not dismiss it');
  await p.keyboard.press('Tab');
  ok(await p.evaluate(() => document.getElementById('ppd-banner').contains(document.activeElement)), 'focus stays inside');
  await p.screenshot({ path: './shot-modal.png' });
  await p.context().close();

  console.log('K. Box position, mobile');
  setOpt({ position: 'box' });
  p = await page(browser, { ctx: { viewport: { width: 375, height: 740 } } });
  await p.goto(POST);
  const box = await p.locator('#ppd-banner').boundingBox();
  ok(box && box.width <= 375 && box.x >= 0, 'fits the phone width');
  ok(await p.evaluate(() => document.documentElement.scrollWidth <= 375), 'no horizontal scroll');
  const btn = await p.locator('#ppd-banner [data-ppd-action="accept"]').boundingBox();
  ok(btn.height >= 44, 'touch target at least 44px');
  await p.screenshot({ path: './shot-mobile.png' });
  await p.click('#ppd-banner [data-ppd-action="customize"]');
  await p.screenshot({ path: './shot-prefs-mobile.png' });
  await p.context().close();
  setOpt({ position: 'bar' });

  console.log('L. REST validation');
  const bad = await fetch(BASE + '/wp-json/ppd/v1/consent', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: 'x', action: 'accept_all', categories: {}, regime: 'optin' }) });
  ok(bad.status === 400, 'invalid id rejected (400)');
  const evil = await fetch(BASE + '/wp-json/ppd/v1/consent', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: '11111111-2222-4333-8444-555555555555', action: 'custom', categories: { analytics: 1, '<x>': 1 }, regime: 'optin', url: 'https://evil.example/?email=a@b.c' }) });
  ok(evil.status === 201, 'valid record accepted (201)');
  const row = DB("SELECT categories, url FROM wp_ppd_consent_log WHERE consent_id='11111111-2222-4333-8444-555555555555'");
  ok(/^analytics\t?\s*$/.test(row), 'unknown category and foreign URL not stored (' + row.replace(/\t/g, '|') + ')');
  const ipRow = DB('SELECT ip_hash FROM wp_ppd_consent_log LIMIT 1');
  ok(/^[0-9a-f]{64}$/.test(ipRow), 'IP stored only as a hash');
  let limited = false;
  for (let i = 0; i < 35; i++) {
    const r = await fetch(BASE + '/wp-json/ppd/v1/consent', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: '11111111-2222-4333-8444-555555555555', action: 'custom', categories: {}, regime: 'optin' }) });
    if (r.status === 429) { limited = true; break; }
  }
  ok(limited, 'rate limit returns 429');
  const geo = await fetch(BASE + '/wp-json/ppd/v1/geo', { headers: { 'CF-IPCountry': 'US' } });
  ok((await geo.json()).regime === 'optout' && /no-store/.test(geo.headers.get('cache-control')), 'geo endpoint: US opt-out, not cached');
  WP("transient delete --all");

  await browser.close();
  console.log(`\n${passes} passed, ${fails} failed`);
  process.exit(fails ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(2); });
