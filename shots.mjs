// Screen-by-screen screenshots through the Chrome DevTools Protocol (Node 22's built-in WebSocket).
// Usage: node shots.mjs <url> <name> [width=1440] [height=900] [screens=all] [--mobile] [--dark]
// Writes .hallmark/shots/<name>-<n>.png, one per screen, scrolling down the page.
// With SHOT_USER and SHOT_PASS set, it signs in to the local Moodle first (test accounts only).
// Every run starts from a fresh browser profile, so no stale CSS is served from cache.
import { spawn } from 'node:child_process';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const [url, name, w = '1440', h = '900', screens = 'all'] = process.argv.slice(2).filter((a) => !a.startsWith('--'));
const mobile = process.argv.includes('--mobile');
const dark = process.argv.includes('--dark');
const port = 9333;
const profile = 'D:/csfr-lms/.hallmark/chrome-profile';
rmSync(profile, { recursive: true, force: true });
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
  '--headless=new', '--disable-gpu', '--hide-scrollbars', `--remote-debugging-port=${port}`,
  `--user-data-dir=${profile}`, 'about:blank',
], { stdio: 'ignore' });

try {
  let pages;
  for (let i = 0; i < 50 && !pages; i++) {
    try { pages = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json(); } catch { await sleep(200); }
  }
  const ws = new WebSocket(pages.find((p) => p.type === 'page').webSocketDebuggerUrl);
  await new Promise((ok) => ws.addEventListener('open', ok, { once: true }));
  let id = 0;
  const waiting = new Map();
  const events = [];
  ws.addEventListener('message', (m) => {
    const msg = JSON.parse(m.data);
    if (msg.id && waiting.has(msg.id)) { waiting.get(msg.id)(msg); waiting.delete(msg.id); } else if (msg.method) events.push(msg.method);
  });
  const send = (method, params = {}) => new Promise((ok) => { const n = ++id; waiting.set(n, ok); ws.send(JSON.stringify({ id: n, method, params })); });

  await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride', { width: +w, height: +h, deviceScaleFactor: 1, mobile });
  if (dark) await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-color-scheme', value: 'dark' }] });
  const load = async (target) => {
    events.length = 0;
    await send('Page.navigate', { url: target });
    for (let i = 0; i < 150 && !events.includes('Page.loadEventFired'); i++) await sleep(100);
    await sleep(600);
  };
  if (process.env.SHOT_USER) {
    await load('http://localhost:8000/login/index.php');
    events.length = 0;
    await send('Runtime.evaluate', { expression: `(() => {
      document.querySelector('#username').value = ${JSON.stringify(process.env.SHOT_USER)};
      document.querySelector('#password').value = ${JSON.stringify(process.env.SHOT_PASS || '')};
      document.querySelector('#loginbtn').click(); })()` });
    for (let i = 0; i < 150 && !events.includes('Page.loadEventFired'); i++) await sleep(100);
    await sleep(800);
  }
  // --pre=<url>,<url> visits pages first (to walk a flow); the url "here" keeps whatever page login landed on.
  const preArg = process.argv.find((a) => a.startsWith('--pre='));
  for (const pre of preArg ? preArg.slice(6).split(',') : []) await load(pre);
  if (url !== 'here') await load(url);
  const where = await send('Runtime.evaluate', { expression: 'location.pathname + location.search', returnByValue: true });
  console.log(`at ${where.result.result.value}`);
  const evalArg = process.argv.find((a) => a.startsWith('--eval='));
  if (evalArg) {
    const r = await send('Runtime.evaluate', { expression: evalArg.slice(7), returnByValue: true, awaitPromise: true });
    console.log('eval:', JSON.stringify(r.result.result.value ?? r.result.exceptionDetails?.text, null, 1));
  }
  const metrics = await send('Page.getLayoutMetrics');
  const total = Math.ceil(metrics.result.cssContentSize.height);
  const sw = await send('Runtime.evaluate', { expression: 'document.documentElement.scrollWidth', returnByValue: true });
  console.log(`scrollWidth ${sw.result.result.value} at width ${w}${sw.result.result.value > +w ? '  OVERFLOW' : ''}`);
  const count = screens === 'all' ? Math.ceil(total / +h) : +screens;
  mkdirSync('.hallmark/shots', { recursive: true });
  for (let s = 0; s < count; s++) {
    await send('Runtime.evaluate', { expression: `window.scrollTo(0, ${s * +h})` });
    await sleep(250);
    const shot = await send('Page.captureScreenshot', { format: 'png' });
    const file = `.hallmark/shots/${name}-${s + 1}.png`;
    writeFileSync(file, Buffer.from(shot.result.data, 'base64'));
    console.log(file);
  }
  console.log(`page height ${total}px, ${count} screens`);
  ws.close();
} finally {
  chrome.kill();
}
