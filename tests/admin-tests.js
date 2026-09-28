const { chromium } = require('playwright-core');
const { execSync } = require('child_process');
const BASE = process.env.PPD_BASE || 'http://127.0.0.1:8899';
const WP = (cmd) => execSync(`cd ${process.env.PPD_WP_PATH || './wp'} && ${process.env.PPD_WP_CLI || 'wp'} --allow-root ${cmd} 2>/dev/null`).toString().trim();
const opt = () => JSON.parse(WP('option get ppd_settings --format=json') || '{}');
let fails = 0, passes = 0;
const ok = (c, n) => { if (c) { passes++; console.log('  ok  ', n); } else { fails++; console.log('  FAIL', n); } };

(async () => {
  const b = await chromium.launch({ executablePath: process.env.PPD_CHROMIUM || undefined });
  const ctx = await b.newContext({ viewport: { width: 1280, height: 900 }, acceptDownloads: true });
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => r.abort());
  const p = await ctx.newPage();
  const errs = []; p.on('pageerror', (e) => errs.push(e.message));
  await p.goto(BASE + '/wp-login.php');
  await p.fill('#user_login', 'admin'); await p.fill('#user_pass', 'teste123');
  await Promise.all([p.waitForNavigation(), p.click('#wp-submit')]);

  const before = opt();
  for (const tab of ['general', 'appearance', 'texts', 'categories', 'google', 'blocking', 'log']) {
    await p.goto(BASE + '/wp-admin/options-general.php?page=ppd-settings&tab=' + tab);
    const h1 = await p.textContent('.wrap h1');
    const body = await p.textContent('#wpbody-content');
    ok(/Cookies/.test(h1) && !/(Warning|Notice|Fatal error|Deprecated)/.test(body), 'tab ' + tab + ' renders without PHP errors');
  }
  await p.goto(BASE + '/wp-admin/options-general.php?page=ppd-settings&tab=log');
  await p.screenshot({ path: './shot-admin-log.png', fullPage: false });
  ok((await p.locator('table.widefat tbody tr').count()) > 0, 'log table lists records');
  const [dl] = await Promise.all([p.waitForEvent('download'), p.click('a.button:has-text("CSV")')]);
  const csv = require('fs').readFileSync(await dl.path(), 'utf8');
  ok(/^created_at_utc,consent_id/.test(csv) && csv.split('\n').length > 2, 'CSV export works');

  // Save the general tab with GPC off: other tabs must keep their values.
  await p.goto(BASE + '/wp-admin/options-general.php?page=ppd-settings&tab=general');
  await p.uncheck('input[name="ppd_settings[respect_gpc]"]');
  await p.fill('#ppd-optin_countries', 'br, de, xx1, FR');
  await Promise.all([p.waitForNavigation(), p.click('#submit')]);
  let s = opt();
  ok(s.respect_gpc === 0, 'unchecked box saved as 0');
  ok(s.optin_countries === 'BR,DE,FR', 'country list sanitized (' + s.optin_countries + ')');
  ok(s.script_handles === before.script_handles && s.gtm_id === before.gtm_id && s.scripts_analytics === before.scripts_analytics, 'other tabs untouched');
  ok(s.embeds_block === (before.embeds_block ?? 1) && s.cat_marketing === (before.cat_marketing ?? 1) && s.log_enabled === (before.log_enabled ?? 1), 'checkboxes of other tabs untouched');

  // Ask again: version goes up.
  const v = s.policy_version;
  await p.check('input[name="ppd_settings[_renew]"]');
  await Promise.all([p.waitForNavigation(), p.click('#submit')]);
  s = opt();
  ok(String(s.policy_version) === String(parseInt(v, 10) + 1), 'renew bumps policy version ' + v + ' -> ' + s.policy_version);

  // Texts tab: override one text, then check it on the front end.
  await p.goto(BASE + '/wp-admin/options-general.php?page=ppd-settings&tab=texts');
  await p.fill('#ppd-text_title', 'Cookies <b>aqui</b>');
  await Promise.all([p.waitForNavigation(), p.click('#submit')]);
  ok(opt().text_title === 'Cookies aqui', 'text override saved and stripped of HTML');

  // Script box: an author (no unfiltered_html) cannot inject code.
  WP('user create autor autor@example.test --role=editor --user_pass=autor123');
  WP("eval '$r=get_role(\"editor\"); $r->add_cap(\"manage_options\");'");
  require('fs').mkdirSync(`${process.env.PPD_WP_PATH || './wp'}/wp-content/mu-plugins`, { recursive: true });
  require('fs').writeFileSync(`${process.env.PPD_WP_PATH || './wp'}/wp-content/mu-plugins/nofilter.php`, "<?php define('DISALLOW_UNFILTERED_HTML', true);");
  const c2 = await b.newContext(); const p2 = await c2.newPage();
  await p2.goto(BASE + '/wp-login.php');
  await p2.fill('#user_login', 'autor'); await p2.fill('#user_pass', 'autor123');
  await Promise.all([p2.waitForNavigation(), p2.click('#wp-submit')]);
  await p2.goto(BASE + '/wp-admin/options-general.php?page=ppd-settings&tab=categories');
  ok(await p2.locator('#ppd-scripts_analytics').evaluate((e) => e.readOnly), 'script box read-only without unfiltered_html');
  await p2.evaluate(() => { const t = document.getElementById('ppd-scripts_analytics'); t.readOnly = false; t.value = '<script>alert(1)</script>'; });
  await Promise.all([p2.waitForNavigation(), p2.click('#submit')]);
  ok(opt().scripts_analytics === before.scripts_analytics, 'forged script not saved');
  await c2.close();
  require('fs').unlinkSync(`${process.env.PPD_WP_PATH || './wp'}/wp-content/mu-plugins/nofilter.php`);

  // Front end with the admin logged in: nonce sent, record tied to the user.
  const p3 = await ctx.newPage();
  await p3.goto(BASE + '/?p=4');
  ok(await p3.evaluate(() => !!window.ppdConfig.nonce), 'REST nonce only for logged-in users');
  ok((await p3.textContent('#ppd-banner-title')) === 'Cookies aqui', 'text override on front end');
  await p3.click('#ppd-banner [data-ppd-action="accept"]');
  await p3.waitForTimeout(500);
  const exp = WP(`eval 'print_r(ppd_privacy_exporter("a@example.test",1)["data"][0]["data"][1]);'`);
  ok(/accept_all/.test(exp), 'privacy exporter returns the user record');
  const er = WP(`eval 'echo ppd_privacy_eraser("a@example.test")["items_removed"];'`);
  ok(parseInt(er, 10) >= 1, 'privacy eraser removes user records (' + er + ')');
  await p3.goto(BASE + '/wp-admin/options-privacy.php?tab=policyguide');
  const guide = await p3.textContent('#wpbody-content');
  ok(/Consentimento de cookies|Cookie consent/.test(guide) && /ppd_cookie_table/.test(guide), 'privacy policy guide text registered');
  ok(errs.length === 0, 'no admin JS errors');
  await b.close();

  const log = require('fs').existsSync((process.env.PPD_DEBUG_LOG || './wp/wp-content/debug.log')) ? require('fs').readFileSync((process.env.PPD_DEBUG_LOG || './wp/wp-content/debug.log'), 'utf8') : '';
  const own = log.split('\n').filter((l) => /privacy-policy-disclaimer|ppd_/.test(l));
  ok(own.length === 0, 'no PHP notices from the file in debug.log' + (own.length ? ': ' + own.slice(0, 3).join(' || ') : ''));
  console.log(`\n${passes} passed, ${fails} failed`);
  process.exit(fails ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(2); });
