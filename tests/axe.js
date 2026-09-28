const { chromium } = require('playwright-core'); const fs = require('fs');
const axe = fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
(async () => { const b = await chromium.launch({ executablePath: process.env.PPD_CHROMIUM || undefined });
 for (const pos of ['bar']) {
 const c = await b.newContext(); await c.route(/^https?:\/\/(?!127\.0\.0\.1)/, r => r.abort()); const p = await c.newPage();
 await p.goto((process.env.PPD_BASE || 'http://127.0.0.1:8899') + '/?p=' + (process.env.PPD_POST_ID || 4)); await p.addScriptTag({ content: axe });
 const run = async (label, sel) => { const r = await p.evaluate(async (s) => (await axe.run(s, { runOnly: ['wcag2a','wcag2aa','wcag21a','wcag21aa'] })).violations.map(v => v.id + ' (' + v.nodes.length + '): ' + v.nodes.slice(0,2).map(n=>n.target.join(' ')).join(' ; ')), sel);
   console.log(label, r.length ? r : 'no violations'); };
 await run('banner', '#ppd-root'); await run('embed placeholder', '.ppd-embed');
 await p.click('#ppd-banner [data-ppd-action="customize"]'); await run('preferences dialog', '#ppd-prefs');
 await c.close(); }
 await b.close(); })();
