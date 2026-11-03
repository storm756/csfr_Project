/* ==========================================================================
   Community Cyber First Responder · Assessment Studio engine
   Shared by every module. Reads window.CCFR_MODULE (js/questions.js).
   Tap-only formats: cards, spot, order, sort, pick, match, settings,
   chat, dial, build, hotspot, crossword. Every challenge scores 0–1 with partial credit.
   ========================================================================== */
(function () {
  'use strict';
  var M = window.CCFR_MODULE;
  if (!M) { document.body.innerHTML = '<p style="padding:20px">questions.js did not load.</p>'; return; }

  var app = document.getElementById('app');
  var state = { i: 0, res: [], started: false, done: false };
  var STORE = 'ccfr-best-' + M.code;

  /* ---------------- helpers ---------------- */
  function h(tag, attrs, html) {
    var e = document.createElement(tag);
    if (attrs) for (var k in attrs) {
      if (k === 'class') e.className = attrs[k];
      else if (k === 'text') e.textContent = attrs[k];
      else if (k.slice(0, 2) === 'on') e.addEventListener(k.slice(2), attrs[k]);
      else e.setAttribute(k, attrs[k]);
    }
    if (html != null) e.innerHTML = html;
    return e;
  }
  function shuffle(a) {
    a = a.slice();
    for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = a[i]; a[i] = a[j]; a[j] = t; }
    return a;
  }
  /* shuffle indices but never leave them in the original order */
  function mix(n) {
    var idx = []; for (var i = 0; i < n; i++) idx.push(i);
    if (n < 2) return idx;
    var s; var guard = 0;
    do { s = shuffle(idx); guard++; } while (s.every(function (v, k) { return v === k; }) && guard < 20);
    return s;
  }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function fmt1(x) { return (Math.round(x * 10) / 10).toFixed(1).replace(/\.0$/, ''); }
  function store(get, val) {
    try { if (get) return JSON.parse(localStorage.getItem(STORE) || 'null'); localStorage.setItem(STORE, JSON.stringify(val)); } catch (e) { return null; }
  }
  function total() { return state.res.reduce(function (s, r) { return s + (r ? r.score : 0); }, 0); }
  function gateCount() { return state.res.filter(function (r) { return r && r.gate; }).length; }

  var FORMAT = {
    cards: 'Verdict cards', spot: 'Spot the signs', order: 'Put in order', sort: 'Sort board', pick: 'Pack the bag',
    match: 'Match up', settings: 'Settings simulator', chat: 'Live conversation', dial: 'Dial it in', build: 'Fill the slots',
    hotspot: 'Tap the picture', mc: 'Choose one', crossword: 'Crossword'
  };
  function fmtName(q) { return q.format || FORMAT[q.type] || q.type; }

  /* ---------------- theme toggle ---------------- */
  function themeBtn() {
    var b = h('button', { class: 'themebtn ghost', type: 'button', 'aria-label': 'Switch light or dark theme' }, 'Theme');
    b.onclick = function () {
      var r = document.documentElement;
      var dark = r.getAttribute('data-theme') === 'dark' ||
        (!r.getAttribute('data-theme') && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
      r.setAttribute('data-theme', dark ? 'light' : 'dark');
    };
    return b;
  }

  /* ---------------- frame ---------------- */
  function frame() {
    app.innerHTML = '';
    var tb = h('div', { class: 'topbar' });
    tb.appendChild(h('div', { class: 'badge', 'aria-hidden': 'true' }, M.icon || M.num));
    tb.appendChild(h('div', { class: 't' }, '<div class="kicker">' + esc(M.kicker || ('Module ' + M.num + ' assessment')) + '</div><div class="mt">' + esc(M.title) + '</div>'));
    var hubLink = h('a', { class: 'hublink', href: '../../index.html', 'aria-label': 'Back to all modules' }, '&#8962; All modules');
    hubLink.addEventListener('click', function (e) {
      if (state.started && !state.done && !confirm('Leave this assessment? Your answers so far will be lost.')) e.preventDefault();
    });
    tb.appendChild(hubLink);
    if (state.started) tb.appendChild(h('div', { class: 'scorepill', 'aria-live': 'polite' }, 'Score ' + fmt1(total()) + ' / ' + M.questions.length));
    tb.appendChild(themeBtn());
    app.appendChild(tb);
    if (state.started) {
      var pg = h('div', { class: 'progress', 'aria-hidden': 'true' });
      M.questions.forEach(function (q, k) {
        var r = state.res[k], c = '';
        if (r) c = r.score >= .999 ? 'done' : (r.score > 0 ? 'part' : 'zero'); else if (k === state.i) c = 'now';
        pg.appendChild(h('i', { class: c }));
      });
      app.appendChild(pg);
    }
    var body = h('div', { id: 'body' });
    app.appendChild(body);
    app.appendChild(h('div', { class: 'help' }, 'Scammed? Call 1930 or file a complaint at cybercrime.gov.in'));
    app.appendChild(h('div', { class: 'foot' }, 'Community Cyber First Responder · CyberPeace Foundation × SAKEC, Mumbai · Practice assessment · Fictional cases only'));
    return body;
  }

  /* ---------------- intro ---------------- */
  function intro() {
    state.started = false;
    var b = frame();
    var c = h('div', { class: 'card hero' });
    c.innerHTML = '<span class="tag">' + esc(M.kicker || ('Module ' + M.num)) + '</span>' +
      '<h1>' + esc(M.gameTitle || M.title) + '</h1>' +
      '<p class="sub">' + (M.tagline || '') + '</p>' +
      (M.story ? '<div class="scene"><b>Your shift</b>' + M.story + '</div>' : '') +
      '<div class="stats"><div class="stat"><b>' + M.questions.length + '</b><span>challenges</span></div>' +
      '<div class="stat"><b>' + (M.minutes || 15) + '</b><span>minutes, about</span></div>' +
      '<div class="stat"><b>0</b><span>typing needed</span></div></div>';
    var best = store(true);
    if (best && typeof best.pct === 'number') c.appendChild(h('p', { class: 'small' }, '<b>Your best so far on this device:</b> ' + best.pct + '% (' + esc(best.band) + ')'));
    var ul = h('ol', { class: 'chlist' });
    M.questions.forEach(function (q) {
      ul.appendChild(h('li', null, '<div><b>' + esc(q.title) + '</b><span class="fmt">' + esc(fmtName(q)) + (q.mod ? ' · ' + esc(q.mod) : '') + '</span></div>'));
    });
    c.appendChild(h('h3', null, 'The challenges'));
    c.appendChild(ul);
    c.appendChild(h('h3', null, 'How it works'));
    c.appendChild(h('ul', { class: 'rules' },
      '<li>Every challenge is worth 1 point, with partial credit.</li>' +
      '<li>Press <b>Check</b> when you are ready. You see the reason for every answer.</li>' +
      '<li><b>Safety gate:</b> some choices would put a citizen at risk (for example asking for an OTP, copying an intimate image or paying a ransom). Choosing one marks the attempt <b>Not yet competent</b>, whatever the score.</li>' +
      '<li>Bands follow the programme booklet: A+ 90% and above · A 80% · B 60% · C 50% · Not yet below 50%.</li>'));
    var go = h('button', { class: 'primary', type: 'button' }, 'Start the assessment');
    go.onclick = function () { state.started = true; state.done = false; state.i = 0; state.res = []; if (M.reshuffle) M.reshuffle(); question(); };
    var a = h('div', { class: 'actions' }); a.appendChild(go); c.appendChild(a);
    b.appendChild(c);
    go.focus();
  }

  /* ---------------- question shell ---------------- */
  function question() {
    var q = M.questions[state.i];
    var b = frame();
    var c = h('section', { class: 'card', 'aria-labelledby': 'qh' });
    c.innerHTML = '<span class="tag">Challenge ' + (state.i + 1) + ' of ' + M.questions.length + '</span>' +
      '<span class="tag alt">' + esc(fmtName(q)) + '</span>' + (q.mod ? '<span class="tag alt">' + esc(q.mod) + '</span>' : '') +
      '<h2 id="qh" tabindex="-1">' + esc(q.title) + '</h2>' +
      (q.scene ? '<div class="scene"><b>' + esc(q.sceneLabel || 'Situation') + '</b>' + q.scene + '</div>' : '') +
      (q.ask ? '<p class="ask">' + q.ask + '</p>' : '');
    var area = h('div', { class: 'area' });
    c.appendChild(area);
    var act = h('div', { class: 'actions' });
    c.appendChild(act);
    b.appendChild(c);
    var api = {
      q: q, area: area, act: act, card: c,
      done: function (score, fbHtml, gate, cls) { finish(c, act, score, fbHtml, gate, cls); }
    };
    (TYPES[q.type] || function () { area.textContent = 'Unknown format ' + q.type; })(api);
    var hd = document.getElementById('qh'); if (hd) hd.focus({ preventScroll: false });
    window.scrollTo(0, 0);
  }

  function verdictHead(score) {
    if (score >= .999) return ['ok', 'Spot on.'];
    if (score >= .5) return ['part', 'Partly right.'];
    if (score > 0) return ['part', 'Some of it.'];
    return ['no', 'Not yet.'];
  }

  function finish(c, act, score, fbHtml, gate, cls) {
    score = Math.max(0, Math.min(1, score || 0));
    state.res[state.i] = { score: score, gate: !!gate };
    var v = verdictHead(score);
    var fb = h('div', { class: 'fb ' + (cls || v[0]), role: 'status', 'aria-live': 'polite' },
      '<span class="h">' + v[1] + ' ' + fmt1(score) + ' / 1 point</span>' + (fbHtml || ''));
    c.insertBefore(fb, act);
    if (gate) c.insertBefore(h('div', { class: 'gatewarn' }, 'Safety gate: ' + (typeof gate === 'string' ? gate : 'one of your choices would put the citizen at risk in real life.')), act);
    act.innerHTML = '';
    var last = state.i === M.questions.length - 1;
    var nx = h('button', { class: 'primary', type: 'button' }, last ? 'See my result' : 'Next challenge');
    nx.onclick = function () { if (last) end(); else { state.i++; question(); } };
    act.appendChild(nx);
    var sp = document.querySelector('.scorepill'); if (sp) sp.textContent = 'Score ' + fmt1(total()) + ' / ' + M.questions.length;
    var pg = document.querySelectorAll('.progress i')[state.i];
    if (pg) pg.className = score >= .999 ? 'done' : (score > 0 ? 'part' : 'zero');
    fb.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    nx.focus({ preventScroll: true });
  }

  function checkBtn(api, label, fn, enabled) {
    var b = h('button', { class: 'primary', type: 'button' }, label || 'Check my answer');
    b.disabled = enabled === false;
    b.onclick = fn;
    api.act.innerHTML = '';
    api.act.appendChild(b);
    return b;
  }
  function list(items) { return items.length ? '<ul>' + items.map(function (x) { return '<li>' + x + '</li>'; }).join('') + '</ul>' : ''; }

  /* ======================================================================
     FORMATS
     ====================================================================== */
  var TYPES = {};

  /* ---- verdict cards: one card at a time, swipe or tap ---- */
  TYPES.cards = function (api) {
    var q = api.q, items = q.keepOrder ? q.items.slice() : mix(q.items.length).map(function (k) { return q.items[k]; });
    var i = 0, right = 0, gate = false, notes = [];
    var two = q.labels.length === 2;
    function show() {
      api.area.innerHTML = ''; api.act.innerHTML = '';
      if (i >= items.length) {
        var sc = right / items.length;
        api.done(sc, '<p>You got ' + right + ' of ' + items.length + ' cards right.</p>' + (q.why ? '<p>' + q.why + '</p>' : '') + list(notes), gate ? (q.gateMsg || true) : false);
        return;
      }
      var it = items[i];
      var deck = h('div', { class: 'deck' });
      var vc = h('div', { class: 'vcard' + (q.mono ? ' mono' : ''), 'aria-live': 'polite' },
        '<span class="count">Card ' + (i + 1) + ' of ' + items.length + '</span>' + it.t);
      deck.appendChild(vc);
      api.area.appendChild(deck);
      var row = h('div', { class: 'row' });
      var btns = q.labels.map(function (L, k) {
        var b = h('button', { class: L.c || '', type: 'button' }, esc(L.t));
        b.onclick = function () { answer(k); };
        row.appendChild(b); return b;
      });
      api.area.appendChild(row);
      if (two) {
        api.area.appendChild(h('p', { class: 'swipehint' }, 'Tap a button, or swipe the card: left for “' + esc(q.labels[0].t) + '”, right for “' + esc(q.labels[1].t) + '”.'));
        swipe(vc, function (dir) { answer(dir < 0 ? 0 : 1); });
      }
      var answered = false;
      function answer(k) {
        if (answered) return; answered = true;
        btns.forEach(function (b) { b.disabled = true; });
        var good = k === it.a;
        if (good) right++; else { notes.push('<b>' + strip(it.t) + '</b> → ' + esc(q.labels[it.a].t)); if (it.gate) gate = true; }
        vc.style.transform = two ? 'translateX(' + (k === 0 ? '-' : '') + '14px)' : '';
        var f = h('div', { class: 'fb ' + (good ? 'ok' : 'no') }, '<span class="h">' + (good ? 'Correct: ' : 'Not quite. Answer: ') + esc(q.labels[it.a].t) + '</span>' + (it.why || ''));
        api.area.appendChild(f);
        if (it.gate && !good) api.area.appendChild(h('div', { class: 'gatewarn' }, 'Safety gate: this choice would put the citizen at risk.'));
        var n = h('button', { class: 'primary', type: 'button' }, i + 1 < items.length ? 'Next card' : 'Finish this challenge');
        n.onclick = function () { i++; show(); };
        api.act.appendChild(n); n.focus({ preventScroll: true });
      }
    }
    show();
  };
  function strip(s) { var d = document.createElement('div'); d.innerHTML = s; return esc(d.textContent); }
  function swipe(el, cb) {
    var x0 = null, dx = 0, id = null;
    el.addEventListener('pointerdown', function (e) { x0 = e.clientX; dx = 0; id = e.pointerId; el.classList.add('dragging'); try { el.setPointerCapture(id); } catch (err) { } });
    el.addEventListener('pointermove', function (e) { if (x0 === null) return; dx = e.clientX - x0; el.style.transform = 'translateX(' + dx + 'px) rotate(' + (dx / 25) + 'deg)'; });
    function up() {
      if (x0 === null) return; el.classList.remove('dragging');
      if (Math.abs(dx) > 90) cb(dx); else el.style.transform = '';
      x0 = null;
    }
    el.addEventListener('pointerup', up); el.addEventListener('pointercancel', up);
  }

  /* ---- mc: one question, one tap. Used for quick verdicts, colour triage and choose-one ---- */
  TYPES.mc = function (api) {
    var q = api.q, tri = q.opts.some(function (o) { return o && o.c; }), answered = false;
    var opts = q.opts.map(function (o, k) { return { t: typeof o === 'string' ? o : o.t, c: typeof o === 'string' ? '' : (o.c || ''), k: k }; });
    if (!q.keepOrder) opts = shuffle(opts);
    var wrap = h('div', { class: tri ? 'row' : 'pool', role: 'group', 'aria-label': 'Choose one answer' });
    var btns = opts.map(function (o) {
      var b = h('button', { class: o.c, type: 'button' }, esc(o.t));
      b.onclick = function () {
        if (answered) return; answered = true;
        var good = o.k === q.a;
        btns.forEach(function (x, j) { x.disabled = true; if (opts[j].k === q.a) x.classList.add('good'); });
        if (!good) b.classList.add('bad');
        var best = q.opts[q.a], bt = typeof best === 'string' ? best : best.t;
        api.done(good ? 1 : 0, '<p>' + (good ? 'Correct.' : 'Not quite. Best answer: <b>' + esc(bt) + '</b>.') + '</p>' + (q.why ? '<p>' + q.why + '</p>' : ''));
      };
      wrap.appendChild(b); return b;
    });
    api.area.appendChild(wrap);
  };

  /* ---- spot: tap the flagged phrases in a message ---- */
  TYPES.spot = function (api) {
    var q = api.q, flags = 0;
    var html = q.text.replace(/\[\[(!?)([\s\S]+?)\]\]/g, function (m, f, s) {
      if (f) flags++;
      return '<span class="tap" role="button" tabindex="0" aria-pressed="false" data-f="' + (f ? 1 : 0) + '">' + s + '</span>';
    });
    var box = h('div', { class: 'msg ' + (q.style || '') + (q.mode === 'redact' ? ' redact' : '') }, html);
    api.area.appendChild(box);
    var cnt = h('p', { class: 'counter' }, '');
    api.area.appendChild(cnt);
    var taps = box.querySelectorAll('.tap');
    function upd() { var n = box.querySelectorAll('.tap.pick').length; cnt.textContent = n + ' selected' + (q.showCount ? ' · look for ' + flags : ''); }
    taps.forEach(function (t) {
      function tog() { if (t.dataset.locked) return; t.classList.toggle('pick'); t.setAttribute('aria-pressed', t.classList.contains('pick')); upd(); }
      t.onclick = tog;
      t.onkeydown = function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tog(); } };
    });
    upd();
    checkBtn(api, q.mode === 'redact' ? 'Check my redactions' : 'Check my answer', function () {
      var hit = 0, fal = 0;
      taps.forEach(function (t) {
        var f = t.dataset.f === '1', p = t.classList.contains('pick');
        t.dataset.locked = 1; t.classList.remove('pick');
        if (f && p) { hit++; t.classList.add('hit'); } else if (f) t.classList.add('miss'); else if (p) { fal++; t.classList.add('false'); }
      });
      var sc = Math.max(0, hit - fal) / flags;
      var gate = q.gateIfMissed && hit < flags ? q.gateIfMissed : false;
      api.done(sc, '<p>You found ' + hit + ' of ' + flags + (fal ? ' and tapped ' + fal + ' that ' + (fal === 1 ? 'was' : 'were') + ' fine' : '') + '. Green = found · dashed red = missed · struck through = not a problem.</p>' + (q.why ? '<p>' + q.why + '</p>' : '') + list(q.notes || []), gate);
    });
  };

  /* ---- order: build the sequence by tapping ---- */
  TYPES.order = function (api) {
    var q = api.q, n = q.items.length, pool = mix(n), seq = [];
    function draw() {
      api.area.innerHTML = '';
      api.area.appendChild(h('div', { class: 'zone-label' }, 'Your order'));
      var ol = h('ol', { class: 'seq placed' });
      if (!seq.length) ol.appendChild(h('li', { class: 'empty' }, 'Tap the steps below in the order you would do them.'));
      seq.forEach(function (k, pos) {
        var li = h('li'); var b = h('button', { type: 'button', 'aria-label': 'Step ' + (pos + 1) + ': ' + strip(q.items[k].t) + '. Tap to remove.' }, '<span class="n">' + (pos + 1) + '</span><span>' + q.items[k].t + '</span>');
        b.onclick = function () { seq.splice(pos, 1); pool.push(k); draw(); };
        li.appendChild(b); ol.appendChild(li);
      });
      api.area.appendChild(ol);
      if (pool.length) {
        api.area.appendChild(h('div', { class: 'zone-label' }, 'Steps to place'));
        var pl = h('div', { class: 'pool' });
        pool.forEach(function (k, j) {
          var b = h('button', { type: 'button' }, q.items[k].t);
          b.onclick = function () { pool.splice(j, 1); seq.push(k); draw(); };
          pl.appendChild(b);
        });
        api.area.appendChild(pl);
      }
      chk.disabled = seq.length !== n;
    }
    var chk = checkBtn(api, 'Check my order', function () {
      var right = 0;
      var btns = api.area.querySelectorAll('.seq button');
      seq.forEach(function (k, pos) { var g = k === pos; if (g) right++; btns[pos].classList.add(g ? 'good' : 'bad'); btns[pos].disabled = true; });
      var correct = '<ol>' + q.items.map(function (it) { return '<li><b>' + it.t + '</b>' + (it.why ? ' — ' + it.why : '') + '</li>'; }).join('') + '</ol>';
      api.done(right / n, '<p>' + right + ' of ' + n + ' steps in the right place. The correct order:</p>' + correct + (q.why ? '<p>' + q.why + '</p>' : ''));
    }, false);
    draw();
  };

  /* ---- sort: tap a chip, then tap a bin ---- */
  TYPES.sort = function (api) {
    var q = api.q, order = mix(q.items.length), where = {}, sel = null;
    function draw() {
      api.area.innerHTML = '';
      var left = order.filter(function (k) { return where[k] == null; });
      api.area.appendChild(h('div', { class: 'zone-label' }, left.length ? 'Tap a card, then tap where it belongs (' + left.length + ' left)' : 'All placed. Tap a card to move it.'));
      var pool = h('div', { class: 'chips' });
      left.forEach(function (k) { pool.appendChild(chip(k)); });
      api.area.appendChild(pool);
      var bins = h('div', { class: 'bins' });
      q.bins.forEach(function (B, bi) {
        var bin = h('div', { class: 'bin' + (sel != null ? ' target' : '') });
        var bb = h('button', { class: B.c || '', type: 'button' }, esc(B.t));
        bb.onclick = function () { if (sel == null) return; where[sel] = bi; sel = null; draw(); };
        bin.appendChild(bb);
        if (B.d) bin.appendChild(h('div', { class: 'desc' }, B.d));
        var cs = h('div', { class: 'chips' });
        order.filter(function (k) { return where[k] === bi; }).forEach(function (k) { cs.appendChild(chip(k)); });
        bin.appendChild(cs);
        bins.appendChild(bin);
      });
      api.area.appendChild(bins);
      chk.disabled = left.length > 0;
    }
    function chip(k) {
      var c = h('button', { class: 'chip' + (sel === k ? ' sel' : ''), type: 'button', 'aria-pressed': sel === k ? 'true' : 'false', 'data-k': k }, q.items[k].t);
      c.onclick = function () { sel = sel === k ? null : k; draw(); };
      return c;
    }
    var chk = checkBtn(api, 'Check my board', function () {
      var right = 0, gate = false, notes = [];
      q.items.forEach(function (it, k) {
        var g = where[k] === it.b; if (g) right++; else { if (it.gate) gate = true; notes.push('<b>' + it.t + '</b> → ' + esc(q.bins[it.b].t) + (it.why ? ': ' + it.why : '')); }
      });
      api.area.querySelectorAll('.chip').forEach(function (c) { var k = +c.dataset.k; c.classList.add(where[k] === q.items[k].b ? 'good' : 'bad'); c.disabled = true; });
      api.area.querySelectorAll('.bin > button').forEach(function (b) { b.disabled = true; });
      api.done(right / q.items.length, '<p>' + right + ' of ' + q.items.length + ' placed correctly.' + (notes.length ? ' Fix these:' : '') + '</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''), gate ? (q.gateMsg || true) : false);
    }, false);
    draw();
  };

  /* ---- pick: select everything that belongs ---- */
  TYPES.pick = function (api) {
    var q = api.q, order = mix(q.items.length), on = {};
    var need = q.need || 0;
    var cnt = h('p', { class: 'counter' });
    var grid = h('div', { class: 'tiles' });
    order.forEach(function (k) {
      var it = q.items[k];
      var t = h('button', { class: 'tile', type: 'button', 'aria-pressed': 'false', 'data-k': k }, '<span class="box" aria-hidden="true"></span><span>' + it.t + '</span>');
      t.onclick = function () {
        if (t.disabled) return;
        if (!on[k] && need && count() >= need) { cnt.textContent = 'You can pick only ' + need + '. Untick one first.'; return; }
        on[k] = !on[k]; t.setAttribute('aria-pressed', on[k] ? 'true' : 'false'); upd();
      };
      grid.appendChild(t);
    });
    function count() { var n = 0; for (var k in on) if (on[k]) n++; return n; }
    function upd() { cnt.textContent = need ? count() + ' of ' + need + ' picked' : count() + ' ticked'; chk.disabled = need ? count() !== need : false; }
    api.area.appendChild(grid); api.area.appendChild(cnt);
    var chk = checkBtn(api, q.checkLabel || 'Check my bag', function () {
      var right = 0, gate = false, goodPicked = 0, badPicked = 0, notes = [];
      q.items.forEach(function (it, k) {
        var p = !!on[k];
        if (p === !!it.ok) right++;
        if (p && it.ok) goodPicked++;
        if (p && !it.ok) { badPicked++; if (it.gate) gate = true; }
        if (p !== !!it.ok) notes.push((it.ok ? 'Needed: ' : 'Leave out: ') + '<b>' + it.t + '</b>' + (it.why ? ' — ' + it.why : ''));
      });
      grid.querySelectorAll('.tile').forEach(function (t) { var k = +t.dataset.k, it = q.items[k]; t.disabled = true; if (!!on[k] === !!it.ok) t.classList.add('good'); else t.classList.add('bad'); });
      var sc = need ? Math.max(0, goodPicked - badPicked) / need : right / q.items.length;
      api.done(sc, '<p>' + (need ? goodPicked + ' of your ' + need + ' picks were right.' : right + ' of ' + q.items.length + ' decisions right.') + '</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''), gate ? (q.gateMsg || true) : false);
    });
    upd();
  };

  /* ---- match: tap left, then its partner on the right ---- */
  TYPES.match = function (api) {
    var q = api.q, n = q.pairs.length;
    var rights = q.pairs.map(function (p, k) { return { t: p.r, k: k }; }).concat((q.extra || []).map(function (t) { return { t: t, k: -1 }; }));
    rights = shuffle(rights);
    var lefts = mix(n), link = {}, sel = null;
    var wrap = h('div', { class: 'match' });
    var L = h('div', { class: 'col left' }), R = h('div', { class: 'col right' });
    L.appendChild(h('div', { class: 'zone-label' }, esc(q.leftLabel || 'Situation')));
    R.appendChild(h('div', { class: 'zone-label' }, esc(q.rightLabel || 'Goes with')));
    var lb = {}, rb = [];
    lefts.forEach(function (k) {
      var b = h('button', { type: 'button', 'data-k': k }, q.pairs[k].l + '<span class="pin"></span>');
      b.onclick = function () { sel = k; paint(); };
      lb[k] = b; L.appendChild(b);
    });
    rights.forEach(function (r, j) {
      var b = h('button', { type: 'button' }, r.t + '<span class="pin"></span>');
      b.onclick = function () {
        if (sel == null) { R.querySelector('.zone-label').textContent = 'Tap a situation on the left first'; return; }
        for (var k in link) if (link[k] === j) delete link[k];
        link[sel] = j;
        var nx = lefts.filter(function (k) { return link[k] == null; })[0];
        sel = nx == null ? null : nx; paint();
      };
      rb.push(b); R.appendChild(b);
    });
    wrap.appendChild(L); wrap.appendChild(R);
    api.area.appendChild(h('p', { class: 'small' }, 'Tap an item on the left, then its partner on the right. Tap again to change a pair.'));
    api.area.appendChild(wrap);
    var chk;
    function paint() {
      var colourOf = {};
      lefts.forEach(function (k, pos) { if (link[k] != null) colourOf[k] = pos % 8; });
      lefts.forEach(function (k) {
        var b = lb[k]; b.className = (sel === k ? 'sel ' : '') + (link[k] != null ? 'paired' : '');
        var pin = b.querySelector('.pin'); pin.className = 'pin' + (link[k] != null ? ' p' + colourOf[k] : ''); pin.textContent = link[k] != null ? (lefts.indexOf(k) + 1) : '';
      });
      rb.forEach(function (b, j) {
        var owner = null; for (var k in link) if (link[k] === j) owner = +k;
        b.className = owner != null ? 'paired' : '';
        var pin = b.querySelector('.pin'); pin.className = 'pin' + (owner != null ? ' p' + colourOf[owner] : ''); pin.textContent = owner != null ? (lefts.indexOf(owner) + 1) : '';
      });
      if (chk) chk.disabled = lefts.some(function (k) { return link[k] == null; });
    }
    chk = checkBtn(api, 'Check my pairs', function () {
      var right = 0, notes = [];
      lefts.forEach(function (k) {
        var g = rights[link[k]].k === k; if (g) right++;
        lb[k].classList.add(g ? 'good' : 'bad');
        rb[link[k]].classList.add(g ? 'good' : 'bad');
        if (!g) notes.push('<b>' + q.pairs[k].l + '</b> → ' + q.pairs[k].r + (q.pairs[k].why ? ' (' + q.pairs[k].why + ')' : ''));
      });
      L.querySelectorAll('button').forEach(function (b) { b.disabled = true; }); rb.forEach(function (b) { b.disabled = true; });
      api.done(right / n, '<p>' + right + ' of ' + n + ' pairs right.' + (notes.length ? ' Correct pairs for the rest:' : '') + '</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''));
    }, false);
    sel = lefts[0]; paint();
  };

  /* ---- settings simulator: switches and segmented options ---- */
  TYPES.settings = function (api) {
    var q = api.q, val = q.rows.map(function (r) { return r.start; });
    var dev = h('div', { class: 'device' + (q.wide ? ' wide' : '') });
    dev.appendChild(h('div', { class: 'bar' }, '<span>' + esc(q.screen || 'Settings') + '</span><small>' + esc(q.device || '') + '</small>'));
    var rows = [];
    q.rows.forEach(function (r, k) {
      var row = h('div', { class: 'setrow' });
      var lbl = h('div', { class: 'lbl' }, '<span>' + r.t + (r.sub ? '<small>' + r.sub + '</small>' : '') + '</span>');
      row.appendChild(lbl);
      if (r.kind === 'switch') {
        var sw = h('button', { class: 'switch', type: 'button', role: 'switch', 'aria-checked': val[k] ? 'true' : 'false', 'aria-label': strip(r.t) });
        sw.onclick = function () { if (sw.disabled) return; val[k] = !val[k]; sw.setAttribute('aria-checked', val[k] ? 'true' : 'false'); };
        lbl.appendChild(sw);
      } else {
        var seg = h('div', { class: 'seg', role: 'radiogroup', 'aria-label': strip(r.t) });
        r.opts.forEach(function (o, j) {
          var b = h('button', { type: 'button', role: 'radio', 'aria-checked': val[k] === j ? 'true' : 'false' }, esc(o));
          b.onclick = function () { if (b.disabled) return; val[k] = j; seg.querySelectorAll('button').forEach(function (x, jj) { x.setAttribute('aria-checked', jj === j ? 'true' : 'false'); }); };
          seg.appendChild(b);
        });
        row.appendChild(seg);
      }
      rows.push(row); dev.appendChild(row);
    });
    api.area.appendChild(dev);
    checkBtn(api, q.checkLabel || 'Save and check', function () {
      var right = 0;
      q.rows.forEach(function (r, k) {
        var want = Array.isArray(r.want) ? r.want : [r.want];
        var g = want.some(function (w) { return w === val[k]; });
        if (g) right++;
        rows[k].classList.add(g ? 'good' : 'bad');
        var target = r.kind === 'switch' ? (want[0] ? 'On' : 'Off') : want.map(function (w) { return r.opts[w]; }).join(' or ');
        rows[k].appendChild(h('div', { class: 'why' }, (g ? '✓ ' : '✗ Should be <b>' + esc(target) + '</b>. ') + (r.why || '')));
        rows[k].querySelectorAll('button').forEach(function (b) { b.disabled = true; });
      });
      api.done(right / q.rows.length, '<p>' + right + ' of ' + q.rows.length + ' settings right. The reason for each is shown on the screen above.</p>' + (q.why ? '<p>' + q.why + '</p>' : ''));
    });
  };

  /* ---- chat: a short branching conversation ---- */
  TYPES.chat = function (api) {
    var q = api.q, step = 0, pts = 0, max = q.steps.length * 2, gate = false, notes = [];
    var log = h('div', { class: 'chat', 'aria-live': 'polite' });
    var meter = h('div', { class: 'meter' });
    api.area.appendChild(log); var rep = h('div', { class: 'replies' }); api.area.appendChild(rep); api.area.appendChild(meter);
    function drawMeter() { meter.innerHTML = 'Trust meter '; for (var k = 0; k < max; k++) meter.appendChild(h('i', { class: k < pts ? 'on' : '' })); }
    function next() {
      rep.innerHTML = ''; api.act.innerHTML = '';
      if (step >= q.steps.length) {
        api.done(pts / max, '<p>Conversation score: ' + pts + ' of ' + max + '.</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''), gate ? (q.gateMsg || true) : false);
        return;
      }
      var S = q.steps[step];
      log.appendChild(h('div', { class: 'bub them' }, '<span class="who">' + esc(q.who) + '</span>' + S.say));
      rep.appendChild(h('div', { class: 'zone-label' }, 'What do you say or do?'));
      shuffle(S.replies).forEach(function (r) {
        var b = h('button', { type: 'button' }, r.t);
        b.onclick = function () {
          rep.querySelectorAll('button').forEach(function (x) { x.disabled = true; });
          log.appendChild(h('div', { class: 'bub me' }, '<span class="who">You</span>' + r.t));
          pts += r.s; drawMeter();
          if (r.gate) gate = true;
          var best = S.replies.filter(function (x) { return x.s === 2; })[0];
          if (r.s < 2 && best) notes.push('Better: “' + strip(best.t) + '”');
          var f = h('div', { class: 'fb ' + (r.s === 2 ? 'ok' : r.s === 1 ? 'part' : 'no') }, '<span class="h">' + (r.s === 2 ? 'Good response.' : r.s === 1 ? 'Partly helpful.' : 'This would hurt the case.') + '</span>' + (r.why || ''));
          rep.innerHTML = ''; rep.appendChild(f);
          if (r.gate) rep.appendChild(h('div', { class: 'gatewarn' }, 'Safety gate: this choice would put the citizen at risk.'));
          var c = h('button', { class: 'primary', type: 'button' }, step + 1 < q.steps.length ? 'Continue' : 'End the conversation');
          c.onclick = function () { step++; next(); };
          api.act.appendChild(c); c.focus({ preventScroll: true });
        };
        rep.appendChild(b);
      });
    }
    drawMeter(); next();
  };

  /* ---- dial: set times or numbers with steppers ---- */
  TYPES.dial = function (api) {
    var q = api.q, vals = q.rows.map(function (r) { return r.start; }), boxes = [];
    var wrap = h('div', { class: 'dials' });
    function show(r, v) {
      if (r.kind === 'time') { var m = ((v % 1440) + 1440) % 1440; return ('0' + Math.floor(m / 60)).slice(-2) + ':' + ('0' + (m % 60)).slice(-2) + '<small>' + esc(r.unit || 'IST') + '</small>'; }
      return v + (r.unit ? '<small>' + esc(r.unit) + '</small>' : '');
    }
    q.rows.forEach(function (r, k) {
      var d = h('div', { class: 'dial' }, '<div class="q">' + r.label + '</div>');
      var ctl = h('div', { class: 'ctl' });
      var steps = r.steps || (r.kind === 'time' ? [-60, -10, -5, 5, 10, 60] : [-1, 1]);
      var neg = steps.filter(function (s) { return s < 0; }), pos = steps.filter(function (s) { return s > 0; });
      var val = h('div', { class: 'val', 'aria-live': 'polite' }, show(r, vals[k]));
      function mk(s) {
        var lab = r.kind === 'time' ? ((s > 0 ? '+' : '−') + (Math.abs(s) >= 60 ? (Math.abs(s) / 60) + ' h' : Math.abs(s) + ' min')) : ((s > 0 ? '+' : '−') + Math.abs(s));
        var b = h('button', { type: 'button', 'aria-label': (s > 0 ? 'Increase by ' : 'Decrease by ') + Math.abs(s) }, lab);
        b.onclick = function () {
          var v = vals[k] + s;
          if (r.kind !== 'time') { if (r.min != null) v = Math.max(r.min, v); if (r.max != null) v = Math.min(r.max, v); }
          vals[k] = v; val.innerHTML = show(r, v);
        };
        return b;
      }
      neg.forEach(function (s) { ctl.appendChild(mk(s)); });
      ctl.appendChild(val);
      pos.forEach(function (s) { ctl.appendChild(mk(s)); });
      d.appendChild(ctl); boxes.push(d); wrap.appendChild(d);
    });
    api.area.appendChild(wrap);
    checkBtn(api, 'Lock in my answers', function () {
      var right = 0;
      q.rows.forEach(function (r, k) {
        var v = vals[k], a = r.answer;
        if (r.kind === 'time') { v = ((v % 1440) + 1440) % 1440; a = ((a % 1440) + 1440) % 1440; }
        var g = Math.abs(v - a) <= (r.tol || 0);
        if (g) right++;
        boxes[k].classList.add(g ? 'good' : 'bad');
        boxes[k].querySelectorAll('button').forEach(function (b) { b.disabled = true; });
        boxes[k].appendChild(h('p', { class: 'small' }, (g ? '✓ ' : '✗ Answer: <b>' + show({ kind: r.kind, unit: '' }, r.answer).replace(/<small>.*<\/small>/, '') + ' ' + esc(r.unit || '') + '</b>. ') + (r.why || '')));
      });
      api.done(right / q.rows.length, '<p>' + right + ' of ' + q.rows.length + ' right.</p>' + (q.why ? '<p>' + q.why + '</p>' : ''));
    });
  };

  /* ---- build: fill each slot from a shared pool of chips ---- */
  TYPES.build = function (api) {
    var q = api.q, fill = q.slots.map(function () { return null; }), cur = 0;
    var order = mix(q.chips.length);
    function draw() {
      api.area.innerHTML = '';
      var sl = h('div', { class: 'slots' });
      q.slots.forEach(function (s, k) {
        var row = h('div', { class: 'slot' });
        row.appendChild(h('div', { class: 'sl' }, esc(s.label)));
        var b = h('button', { type: 'button', class: (fill[k] != null ? 'filled ' : '') + (cur === k ? 'target' : ''), 'aria-label': s.label + ': ' + (fill[k] != null ? strip(q.chips[fill[k]].t) : 'empty') }, fill[k] != null ? q.chips[fill[k]].t : '<span class="empty">Tap, then choose a card below</span>');
        b.onclick = function () { if (fill[k] != null) { fill[k] = null; } cur = k; draw(); };
        row.appendChild(b); sl.appendChild(row);
      });
      api.area.appendChild(sl);
      var used = fill.filter(function (x) { return x != null; });
      api.area.appendChild(h('div', { class: 'zone-label' }, cur != null ? 'Cards for: ' + esc(q.slots[cur].label) : 'Tap a slot to change it'));
      var pool = h('div', { class: 'pool' });
      order.forEach(function (c) {
        if (used.indexOf(c) >= 0) return;
        if (cur != null && q.slots[cur].chips && q.slots[cur].chips.indexOf(c) < 0) return;
        var b = h('button', { type: 'button' }, q.chips[c].t);
        b.onclick = function () {
          if (cur == null) return;
          fill[cur] = c;
          var nx = fill.indexOf(null); cur = nx < 0 ? null : nx; draw();
        };
        pool.appendChild(b);
      });
      api.area.appendChild(pool);
      chk.disabled = fill.indexOf(null) >= 0;
    }
    var chk = checkBtn(api, q.checkLabel || 'Check my answer', function () {
      var right = 0, gate = false, notes = [];
      var btns = api.area.querySelectorAll('.slot button');
      q.slots.forEach(function (s, k) {
        var ok = Array.isArray(s.a) ? s.a.indexOf(fill[k]) >= 0 : fill[k] === s.a;
        if (ok) right++; else notes.push('<b>' + esc(s.label) + '</b>: ' + q.chips[Array.isArray(s.a) ? s.a[0] : s.a].t);
        if (q.chips[fill[k]].gate) gate = true;
        btns[k].classList.add(ok ? 'good' : 'bad'); btns[k].disabled = true;
      });
      api.area.querySelectorAll('.pool button').forEach(function (b) { b.disabled = true; });
      api.done(right / q.slots.length, '<p>' + right + ' of ' + q.slots.length + ' slots right.' + (notes.length ? ' Correct cards for the rest:' : '') + '</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''), gate ? (q.gateMsg || true) : false);
    }, false);
    draw();
  };

  /* ---- hotspot: tap regions of an illustration ---- */
  TYPES.hotspot = function (api) {
    var q = api.q;
    var box = h('div', { class: 'hot' }, q.svg);
    api.area.appendChild(box);
    var cnt = h('p', { class: 'counter' }); api.area.appendChild(cnt);
    if (q.legend) api.area.appendChild(h('p', { class: 'legend' }, q.legend));
    var spots = box.querySelectorAll('[data-h]');
    function upd() { cnt.textContent = box.querySelectorAll('[data-h].sel').length + ' selected'; }
    spots.forEach(function (s) {
      var id = s.getAttribute('data-h'), info = q.spots[id] || {};
      s.setAttribute('tabindex', '0'); s.setAttribute('role', 'button'); s.setAttribute('aria-pressed', 'false');
      s.setAttribute('aria-label', info.label || id);
      function tog() { if (s.dataset.locked) return; s.classList.toggle('sel'); s.setAttribute('aria-pressed', s.classList.contains('sel')); upd(); }
      s.addEventListener('click', tog);
      s.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tog(); } });
    });
    upd();
    checkBtn(api, 'Check my taps', function () {
      var hit = 0, fal = 0, need = 0, gate = false, notes = [];
      spots.forEach(function (s) {
        var id = s.getAttribute('data-h'), info = q.spots[id] || {}, p = s.classList.contains('sel');
        s.dataset.locked = 1; s.classList.remove('sel');
        if (info.ok) need++;
        if (info.ok && p) { hit++; s.classList.add('hit'); notes.push('✓ <b>' + esc(info.label) + '</b> — ' + (info.why || '')); }
        else if (info.ok) { s.classList.add('miss'); notes.push('Missed: <b>' + esc(info.label) + '</b> — ' + (info.why || '')); }
        else if (p) { fal++; s.classList.add('false'); if (info.gate) gate = true; notes.push('Not needed: <b>' + esc(info.label) + '</b> — ' + (info.why || '')); }
      });
      api.done(Math.max(0, hit - fal) / need, '<p>You found ' + hit + ' of ' + need + (fal ? ', with ' + fal + ' wrong tap' + (fal > 1 ? 's' : '') : '') + '.</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''), gate ? (q.gateMsg || true) : false);
    });
  };

  /* ---- crossword: tap a cell or clue, type on the screen keyboard or your own keyboard ---- */
  TYPES.crossword = function (api) {
    var q = api.q, W = q.words, R = 0, C = 0, locked = false;
    W.forEach(function (w) {
      w.a = w.a.toUpperCase(); w.cells = [];
      for (var i = 0; i < w.a.length; i++) {
        var r = w.r + (w.dir === 'D' ? i : 0), c = w.c + (w.dir === 'A' ? i : 0);
        w.cells.push([r, c]); R = Math.max(R, r + 1); C = Math.max(C, c + 1);
      }
    });
    var cell = {}; /* "r,c" -> {r,c,ans,val,num,words:[wordIndex...],el} */
    W.forEach(function (w, wi) {
      w.cells.forEach(function (rc, i) {
        var k = rc[0] + ',' + rc[1];
        if (!cell[k]) cell[k] = { r: rc[0], c: rc[1], ans: w.a.charAt(i), val: '', num: 0, words: [] };
        cell[k].words.push(wi);
      });
    });
    /* standard numbering: row by row, a cell that starts an across or down word gets the next number */
    var n = 0;
    for (var r = 0; r < R; r++) for (var c = 0; c < C; c++) {
      var cl = cell[r + ',' + c]; if (!cl) continue;
      var starts = W.filter(function (w) { return w.r === r && w.c === c; });
      if (starts.length) { n++; cl.num = n; starts.forEach(function (w) { w.n = n; }); }
    }
    var cur = 0, curCell = W[0].cells[0], el = {}, clueBtn = [];
    var banner = h('div', { class: 'xnow', 'aria-live': 'polite' });
    var grid = h('div', { class: 'xgrid', style: '--cols:' + C + ';grid-template-columns:repeat(' + C + ',minmax(0,1fr))' });
    for (var rr = 0; rr < R; rr++) for (var cc = 0; cc < C; cc++) {
      (function (rr, cc) {
        var cl = cell[rr + ',' + cc];
        if (!cl) { grid.appendChild(h('span', { class: 'xb', 'aria-hidden': 'true' })); return; }
        var b = h('button', { type: 'button', class: 'xc', 'data-r': rr, 'data-c': cc }, (cl.num ? '<i>' + cl.num + '</i>' : '') + '<b></b>');
        b.onclick = function () { tapCell(cl); };
        cl.el = b; grid.appendChild(b);
      })(rr, cc);
    }
    function wordLabel(w) { return w.n + ' ' + (w.dir === 'A' ? 'Across' : 'Down') + ' · ' + w.a.length + ' letters'; }
    function paint() {
      Object.keys(cell).forEach(function (k) {
        var cl = cell[k], inw = cl.words.indexOf(cur) >= 0, here = curCell && curCell[0] === cl.r && curCell[1] === cl.c;
        if (!locked) cl.el.className = 'xc' + (inw ? ' inword' : '') + (here ? ' cur' : '');
        cl.el.querySelector('b').textContent = cl.val;
        cl.el.setAttribute('aria-label', (cl.num ? 'Clue number ' + cl.num + ', ' : '') + 'row ' + (cl.r + 1) + ' column ' + (cl.c + 1) + ': ' + (cl.val || 'empty'));
      });
      var w = W[cur];
      banner.innerHTML = '<b>' + esc(wordLabel(w)) + '</b>' + w.clue;
      clueBtn.forEach(function (b, wi) { b.className = 'xclue' + (wi === cur ? ' on' : '') + (isFull(W[wi]) ? ' full' : ''); });
    }
    function isFull(w) { return w.cells.every(function (rc) { return cell[rc[0] + ',' + rc[1]].val; }); }
    function tapCell(cl) {
      if (locked) return;
      var here = curCell && curCell[0] === cl.r && curCell[1] === cl.c;
      if (here && cl.words.length > 1 && cl.words.indexOf(cur) >= 0) cur = cl.words[(cl.words.indexOf(cur) + 1) % cl.words.length];
      else if (cl.words.indexOf(cur) < 0) cur = cl.words[0];
      curCell = [cl.r, cl.c]; paint();
    }
    function pickWord(wi) {
      if (locked) return;
      cur = wi; var w = W[wi], at = w.cells[0];
      for (var i = 0; i < w.cells.length; i++) { if (!cell[w.cells[i][0] + ',' + w.cells[i][1]].val) { at = w.cells[i]; break; } }
      curCell = at; paint();
    }
    function idx() { var w = W[cur]; for (var i = 0; i < w.cells.length; i++) if (w.cells[i][0] === curCell[0] && w.cells[i][1] === curCell[1]) return i; return 0; }
    function typeLetter(ch) {
      if (locked) return;
      var cl = cell[curCell[0] + ',' + curCell[1]]; cl.val = ch;
      var w = W[cur], i = idx(); if (i < w.cells.length - 1) curCell = w.cells[i + 1];
      paint();
    }
    function back() {
      if (locked) return;
      var cl = cell[curCell[0] + ',' + curCell[1]], w = W[cur], i = idx();
      if (cl.val) cl.val = '';
      else if (i > 0) { curCell = w.cells[i - 1]; cell[curCell[0] + ',' + curCell[1]].val = ''; }
      paint();
    }
    function move(dr, dc) {
      var r = curCell[0] + dr, c = curCell[1] + dc, nx = cell[r + ',' + c];
      if (nx) tapCell(nx);
    }
    var wrap = h('div', { class: 'xw' });
    wrap.appendChild(banner); wrap.appendChild(grid);
    /* on-screen keyboard */
    var keys = h('div', { class: 'xkeys', role: 'group', 'aria-label': 'Letter keyboard' });
    ['QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM'].forEach(function (row, ri) {
      var kr = h('div', { class: 'xkrow' });
      row.split('').forEach(function (ch) {
        var kb = h('button', { type: 'button', 'aria-label': ch }, ch);
        kb.onclick = function () { typeLetter(ch); };
        kr.appendChild(kb);
      });
      if (ri === 2) { var bs = h('button', { type: 'button', class: 'xbs', 'aria-label': 'Delete' }, '&#9003;'); bs.onclick = back; kr.appendChild(bs); }
      keys.appendChild(kr);
    });
    wrap.appendChild(keys);
    /* clue lists */
    var cw = h('div', { class: 'xclues' });
    ['A', 'D'].forEach(function (d) {
      var col = h('div', null, '<div class="zone-label">' + (d === 'A' ? 'Across' : 'Down') + '</div>');
      W.map(function (w, wi) { return { w: w, wi: wi }; }).filter(function (x) { return x.w.dir === d; })
        .sort(function (a, b) { return a.w.n - b.w.n; }).forEach(function (x) {
          var b = h('button', { type: 'button', class: 'xclue' }, '<span class="xn">' + x.w.n + '</span><span>' + x.w.clue + '</span>');
          b.onclick = function () { pickWord(x.wi); };
          clueBtn[x.wi] = b; col.appendChild(b);
        });
      cw.appendChild(col);
    });
    wrap.appendChild(cw);
    api.area.appendChild(wrap);
    /* physical keyboard */
    function onKey(e) {
      if (locked || !document.body.contains(grid)) { document.removeEventListener('keydown', onKey); return; }
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      var k = e.key;
      if (/^[a-zA-Z]$/.test(k)) { e.preventDefault(); typeLetter(k.toUpperCase()); }
      else if (k === 'Backspace') { e.preventDefault(); back(); }
      else if (k === 'ArrowRight') { e.preventDefault(); move(0, 1); }
      else if (k === 'ArrowLeft') { e.preventDefault(); move(0, -1); }
      else if (k === 'ArrowDown') { e.preventDefault(); move(1, 0); }
      else if (k === 'ArrowUp') { e.preventDefault(); move(-1, 0); }
    }
    document.addEventListener('keydown', onKey);
    paint();
    checkBtn(api, 'Check my crossword', function () {
      locked = true; document.removeEventListener('keydown', onKey);
      var right = 0, notes = [];
      W.forEach(function (w) {
        var ok = w.cells.every(function (rc, i) { return cell[rc[0] + ',' + rc[1]].val === w.a.charAt(i); });
        if (ok) right++; else notes.push('<b>' + w.n + ' ' + (w.dir === 'A' ? 'Across' : 'Down') + '</b>: ' + esc(w.a) + (w.why ? ' — ' + w.why : ''));
      });
      Object.keys(cell).forEach(function (k) {
        var cl = cell[k], good = cl.val === cl.ans;
        cl.el.className = 'xc ' + (good ? 'good' : 'bad'); cl.el.disabled = true;
        if (!good) cl.el.querySelector('b').textContent = cl.ans;
      });
      keys.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
      clueBtn.forEach(function (b) { b.disabled = true; });
      banner.innerHTML = '<b>Checked</b>Green letters were right. Red cells show the correct letter.';
      api.done(right / W.length, '<p>' + right + ' of ' + W.length + ' words right.' + (notes.length ? ' Fix these:' : '') + '</p>' + list(notes) + (q.why ? '<p>' + q.why + '</p>' : ''));
    });
  };

  /* ---------------- end screen ---------------- */
  function bandOf(pct, gates) {
    if (gates) return ['Not yet competent', 'A safety-gate choice was made. In the real assessment this means re-assessment, whatever the score.'];
    if (pct >= 90) return ['A+ · Gold badge', 'Competent. Excellent work.'];
    if (pct >= 80) return ['A · Silver badge', 'Competent. Strong performance.'];
    if (pct >= 60) return ['B · Module badge', 'Competent. Review the challenges marked amber or red.'];
    if (pct >= 50) return ['C · Competent', 'Competent, with mentor feedback on your weakest area.'];
    return ['Not yet competent', 'Revise the module and try again. Each attempt shuffles the cards.'];
  }

  function end() {
    state.done = true;
    var t = total(), n = M.questions.length, pct = Math.round(100 * t / n), g = gateCount();
    var band = bandOf(pct, g);
    if (window.CCFR_scorm) { try { window.CCFR_scorm(pct, g); } catch (e) {} }
    var prev = store(true);
    if (!prev || pct > prev.pct) store(false, { pct: pct, band: band[0], when: new Date().toISOString().slice(0, 10) });
    var b = frame();
    var c = h('div', { class: 'card hero' });
    var R = 64, C = 2 * Math.PI * R, col = g ? 'var(--red)' : pct >= 60 ? 'var(--grn)' : pct >= 50 ? 'var(--amb)' : 'var(--red)';
    c.innerHTML = '<span class="tag">Result</span><h1>' + esc(M.gameTitle || M.title) + '</h1>' +
      '<div class="result"><div class="ring-wrap"><svg viewBox="0 0 150 150" aria-hidden="true"><circle cx="75" cy="75" r="' + R + '" fill="none" stroke="var(--line)" stroke-width="14"/>' +
      '<circle cx="75" cy="75" r="' + R + '" fill="none" stroke="' + col + '" stroke-width="14" stroke-linecap="round" stroke-dasharray="' + (C * pct / 100) + ' ' + C + '"/></svg><div class="pct">' + pct + '%</div></div>' +
      '<div><div class="band">' + esc(band[0]) + '<small>' + esc(band[1]) + '</small></div>' +
      '<p><b>' + fmt1(t) + ' of ' + n + '</b> points' + (g ? ' · <b style="color:var(--red)">' + g + ' safety-gate choice' + (g > 1 ? 's' : '') + '</b>' : '') + '</p></div></div>';
    b.appendChild(c);

    if (M.groups) {
      var mc = h('div', { class: 'card' }, '<h2>Your profile by module</h2><p class="sub">Use this to choose what to revise before the invigilated final paper.</p>');
      var mb = h('div', { class: 'modbars' });
      M.groups.forEach(function (gr) {
        var qs = M.questions.map(function (q, k) { return { q: q, k: k }; }).filter(function (x) { return x.q.mod === gr.key; });
        var s = qs.reduce(function (a, x) { return a + (state.res[x.k] ? state.res[x.k].score : 0); }, 0), p = qs.length ? Math.round(100 * s / qs.length) : 0;
        mb.appendChild(h('div', { class: 'mb' }, '<span>' + esc(gr.name) + '</span><div class="pbar"><i class="' + (p >= 60 ? '' : p >= 50 ? 'mid' : 'low') + '" style="width:' + p + '%"></i></div><b>' + p + '%</b>'));
      });
      mc.appendChild(mb); b.appendChild(mc);
    }

    var rc = h('div', { class: 'card' }, '<h2>Challenge by challenge</h2>');
    var ul = h('ul', { class: 'review' });
    M.questions.forEach(function (q, k) {
      var r = state.res[k] || { score: 0 }, p = Math.round(100 * r.score);
      ul.appendChild(h('li', null, '<div><span class="nm">' + (k + 1) + '. ' + esc(q.title) + (r.gate ? ' <span class="tag red">gate</span>' : '') + '</span><span class="fm">' + esc(fmtName(q)) + (q.mod ? ' · ' + esc(q.mod) : '') + '</span></div>' +
        '<div><div class="pbar" aria-label="' + p + ' percent"><i class="' + (p >= 99 ? '' : p > 0 ? 'mid' : 'low') + '" style="width:' + Math.max(p, 3) + '%"></i></div><span class="small">' + fmt1(r.score) + ' / 1</span></div>'));
    });
    rc.appendChild(ul);
    if (M.revise) rc.appendChild(h('p', { class: 'small' }, M.revise));
    var a = h('div', { class: 'actions' });
    var again = h('button', { class: 'primary', type: 'button' }, 'Try again (cards reshuffle)');
    again.onclick = function () { state.started = true; state.done = false; state.i = 0; state.res = []; if (M.reshuffle) M.reshuffle(); question(); };
    var home = h('button', { type: 'button' }, 'Back to the start');
    home.onclick = intro;
    var pr = h('button', { type: 'button', class: 'ghost' }, 'Print this result');
    pr.onclick = function () { window.print(); };
    a.appendChild(again); a.appendChild(home); a.appendChild(pr);
    var L = window.CCFR_MODULES || [], at = -1;
    L.forEach(function (x, k) { if (x.code === M.code) at = k; });
    var nx = at >= 0 && L[at + 1];
    var hubBtn = h('a', { class: 'btnlink', href: '../../index.html' }, 'All modules');
    a.appendChild(hubBtn);
    if (nx) a.appendChild(h('a', { class: 'btnlink next', href: '../../' + nx.path }, 'Next: Module ' + esc(nx.num) + ' &rarr;'));
    rc.appendChild(a);
    b.appendChild(rc);
    window.scrollTo(0, 0);
  }

  document.title = (M.gameTitle || M.title) + ' · CCFR Module ' + M.num;
  intro();
})();
