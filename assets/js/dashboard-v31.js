/* Almarah Foundation v3.1 — admin dashboard: counters, charts, live clock, refresh.
   Everything the page needs is already in the HTML; this file only adds motion and interactivity. */
(function () {
  'use strict';
  var root = document.getElementById('dv');
  if (!root) { return; }
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var state = { period: 'daily', measure: 'm' };
  var lastUpdate = Date.now(), busy = false, timer = null;

  /* ---------- number formatting (mirrors the server's Money::short) ---------- */
  function trim(n, d) { var s = n.toFixed(d); return s.indexOf('.') > -1 ? s.replace(/0+$/, '').replace(/\.$/, '') : s; }
  function shortMajor(major, sym) {
    var m = Math.abs(Math.floor(major));
    if (m >= 1e7) { return sym + ' ' + trim(m / 1e7, 2) + ' Cr'; }
    if (m >= 1e5) { return sym + ' ' + trim(m / 1e5, 2) + ' Lac'; }
    if (m >= 1e3) { return sym + ' ' + trim(m / 1e3, 1) + 'k'; }
    return sym + ' ' + m.toLocaleString('en-US');
  }
  function shortMinor(minor, sym) { return shortMajor(Math.floor(Math.abs(minor) / 100), sym); }

  /* ---------- count-up ---------- */
  function countUp(el) {
    var target = parseInt(el.getAttribute('data-count'), 10);
    var finalText = el.textContent;
    if (!isFinite(target) || reduce || target === 0) { return; }
    var money = el.getAttribute('data-kind') === 'money', sym = el.getAttribute('data-symbol') || '';
    var t0 = null, dur = 1300;
    function step(t) {
      if (t0 === null) { t0 = t; }
      var p = Math.min(1, (t - t0) / dur), v = target * (1 - Math.pow(1 - p, 3));
      el.textContent = p >= 1 ? finalText : (money ? shortMajor(v, sym) : Math.round(v).toLocaleString('en-US'));
      if (p < 1) { requestAnimationFrame(step); }
    }
    el.textContent = money ? shortMajor(0, sym) : '0';
    requestAnimationFrame(step);
  }

  /* ---------- clock + greeting ---------- */
  function tick() {
    var d = new Date(), h = d.getHours();
    var g = root.querySelector('[data-greeting]'), c = root.querySelector('[data-clock]');
    if (g) { g.textContent = h < 12 ? 'Good morning' : (h < 17 ? 'Good afternoon' : 'Good evening'); }
    if (c) {
      c.textContent = d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) +
        ' · ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
    var u = root.querySelector('[data-updated]');
    if (u) {
      var s = Math.round((Date.now() - lastUpdate) / 1000);
      u.textContent = s < 10 ? 'Updated just now' : (s < 90 ? 'Updated ' + s + 's ago' : 'Updated ' + Math.round(s / 60) + ' min ago') + ' · refreshes automatically';
    }
  }

  /* ---------- charts ---------- */
  var NS = 'http://www.w3.org/2000/svg';
  function svg(tag, attrs, parent) {
    var n = document.createElementNS(NS, tag);
    for (var k in attrs) { if (Object.prototype.hasOwnProperty.call(attrs, k)) { n.setAttribute(k, attrs[k]); } }
    if (parent) { parent.appendChild(n); }
    return n;
  }
  function niceMax(v) {
    if (v <= 0) { return 1; }
    var p = Math.pow(10, Math.floor(Math.log10(v))), f = v / p;
    return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * p;
  }
  function smooth(pts) {
    if (pts.length < 3) { return 'M' + pts.map(function (p) { return p[0] + ' ' + p[1]; }).join(' L'); }
    var d = 'M' + pts[0][0] + ' ' + pts[0][1];
    for (var i = 0; i < pts.length - 1; i++) {
      var p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2, t = 0.18;
      d += ' C' + (p1[0] + (p2[0] - p0[0]) * t) + ' ' + (p1[1] + (p2[1] - p0[1]) * t) + ' ' +
        (p2[0] - (p3[0] - p1[0]) * t) + ' ' + (p2[1] - (p3[1] - p1[1]) * t) + ' ' + p2[0] + ' ' + p2[1];
    }
    return d;
  }

  function renderChart(card) {
    var host = card.querySelector('[data-chart-canvas]'), dataEl = card.querySelector('[data-chart-data]');
    if (!host || !dataEl) { return; }
    var data; try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }
    var rows = (state.period === 'monthly' ? data.monthly : data.daily) || [];
    if (!rows.length) { state.period = 'daily'; rows = data.daily || []; }
    var key = state.measure, sym = data.symbol || '';
    var sub = card.querySelector('[data-chart-sub]');
    if (sub) { sub.textContent = (state.period === 'monthly' ? 'Monthly totals, last 12 months' : 'Daily totals, last 30 days') + ' · ' + (key === 'm' ? data.currency : 'number of gifts'); }
    host.innerHTML = '';
    var vals = rows.map(function (r) { return r[key] || 0; });
    var total = vals.reduce(function (a, b) { return a + b; }, 0);
    if (!rows.length || total === 0) {
      host.innerHTML = '<div class="dv-chart-empty">No verified donations in this period yet.</div>';
      return;
    }
    var W = Math.max(280, host.clientWidth || 640), H = 280, L = key === 'm' ? 62 : 38, R = 14, T = 14, B = 30;
    var pw = W - L - R, ph = H - T - B, max = niceMax(Math.max.apply(null, vals));
    var s = svg('svg', { viewBox: '0 0 ' + W + ' ' + H, width: W, height: H, role: 'presentation' }, host);
    var defs = svg('defs', {}, s);
    var g1 = svg('linearGradient', { id: 'dvAreaGrad', x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
    svg('stop', { offset: '0%', 'stop-color': '#A92D63', 'stop-opacity': '.38' }, g1); svg('stop', { offset: '100%', 'stop-color': '#A92D63', 'stop-opacity': '0' }, g1);
    var g2 = svg('linearGradient', { id: 'dvBarGrad', x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
    svg('stop', { offset: '0%', 'stop-color': '#C4386F' }, g2); svg('stop', { offset: '100%', 'stop-color': '#6B0F35' }, g2);

    for (var i = 0; i <= 4; i++) {
      var y = T + ph - ph * i / 4, v = max * i / 4;
      svg('line', { x1: L, x2: W - R, y1: y, y2: y, 'class': 'dv-grid-line' }, s);
      var tx = svg('text', { x: L - 8, y: y + 3.5, 'text-anchor': 'end' }, s);
      tx.textContent = key === 'm' ? shortMinor(v, sym) : Math.round(v).toLocaleString('en-US');
    }
    var n = rows.length, step = pw / n, xs = rows.map(function (r, i2) { return L + (state.period === 'monthly' ? step * (i2 + 0.5) : (n === 1 ? pw / 2 : pw * i2 / (n - 1))); });
    var every = state.period === 'monthly' ? (W < 480 ? 2 : 1) : Math.ceil(n / (W < 480 ? 5 : 8));
    rows.forEach(function (r, i3) {
      if (i3 % every !== 0 && i3 !== n - 1) { return; }
      var t = svg('text', { x: xs[i3], y: H - 8, 'text-anchor': 'middle' }, s); t.textContent = r.l;
    });
    var ys = vals.map(function (v2) { return T + ph - (v2 / max) * ph; });
    var maxIdx = vals.indexOf(Math.max.apply(null, vals));
    var cross = svg('line', { y1: T, y2: T + ph, 'class': 'dv-ch-cross' }, s), dot = svg('circle', { r: 5, 'class': 'dv-ch-dot' }, s);

    if (state.period === 'monthly') {
      var bw = Math.min(46, step * 0.62);
      rows.forEach(function (r, i4) {
        var h2 = Math.max(2, (vals[i4] / max) * ph);
        var b = svg('rect', { x: xs[i4] - bw / 2, y: T + ph - h2, width: bw, height: h2, rx: 6, 'class': 'dv-ch-bar' + (i4 === maxIdx ? ' hot' : ''), style: '--k:' + i4 }, s);
        if (reduce) { b.style.animation = 'none'; b.style.transform = 'none'; }
      });
    } else {
      var pts = xs.map(function (x, i5) { return [Math.round(x * 10) / 10, Math.round(ys[i5] * 10) / 10]; });
      var d = smooth(pts);
      var area = svg('path', { d: d + ' L' + pts[pts.length - 1][0] + ' ' + (T + ph) + ' L' + pts[0][0] + ' ' + (T + ph) + ' Z', 'class': 'dv-ch-area' }, s);
      var line = svg('path', { d: d, pathLength: 1, 'class': 'dv-ch-line', style: 'stroke-dasharray:1;stroke-dashoffset:' + (reduce ? 0 : 1) }, s);
      if (!reduce) {
        area.style.opacity = 0; area.style.transition = 'opacity .9s ease .5s';
        line.getBoundingClientRect();
        requestAnimationFrame(function () { line.style.transition = 'stroke-dashoffset 1.4s ease'; line.style.strokeDashoffset = 0; area.style.opacity = 1; });
      }
    }

    var tip = document.createElement('div'); tip.className = 'dv-tip'; host.appendChild(tip);
    var hit = svg('rect', { x: L, y: T, width: pw, height: ph, fill: 'transparent' }, s);
    function show(clientX) {
      var rect = s.getBoundingClientRect(), x = (clientX - rect.left) * (W / rect.width), idx = 0, best = 1e9;
      xs.forEach(function (px, i6) { var dd = Math.abs(px - x); if (dd < best) { best = dd; idx = i6; } });
      var r = rows[idx];
      cross.setAttribute('x1', xs[idx]); cross.setAttribute('x2', xs[idx]); cross.setAttribute('class', 'dv-ch-cross on');
      if (state.period === 'daily') { dot.setAttribute('cx', xs[idx]); dot.setAttribute('cy', ys[idx]); dot.setAttribute('class', 'dv-ch-dot on'); }
      tip.innerHTML = '<b></b><span></span>';
      tip.firstChild.textContent = key === 'm' ? shortMinor(r.m, sym) : r.c.toLocaleString('en-US') + ' gift' + (r.c === 1 ? '' : 's');
      tip.lastChild.textContent = r.l + ' · ' + (key === 'm' ? r.c.toLocaleString('en-US') + ' gift' + (r.c === 1 ? '' : 's') : shortMinor(r.m, sym));
      tip.style.left = (xs[idx] / W * rect.width) + 'px'; tip.style.top = (Math.max(ys[idx], T + 30) / H * rect.height) + 'px'; tip.className = 'dv-tip on';
    }
    function hide() { cross.setAttribute('class', 'dv-ch-cross'); dot.setAttribute('class', 'dv-ch-dot'); tip.className = 'dv-tip'; }
    hit.addEventListener('mousemove', function (e) { show(e.clientX); });
    hit.addEventListener('touchstart', function (e) { show(e.touches[0].clientX); }, { passive: true });
    hit.addEventListener('touchmove', function (e) { show(e.touches[0].clientX); }, { passive: true });
    hit.addEventListener('mouseleave', hide); hit.addEventListener('touchend', hide);
    host.setAttribute('aria-label', (state.period === 'monthly' ? 'Monthly' : 'Daily') + ' verified donations, ' + (key === 'm' ? 'amounts' : 'gift counts') + '. Total ' + (key === 'm' ? shortMinor(total, sym) : total.toLocaleString('en-US')) + '.');
  }

  function syncControls(card) {
    card.querySelectorAll('[data-period]').forEach(function (b) { var on = b.getAttribute('data-period') === state.period; b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
    card.querySelectorAll('[data-measure]').forEach(function (b) { var on = b.getAttribute('data-measure') === state.measure; b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
  }

  /* ---------- per-render setup ---------- */
  function setup() {
    root.querySelectorAll('.dv-num').forEach(countUp);
    root.querySelectorAll('[data-chart]').forEach(function (card) { syncControls(card); renderChart(card); });
    tick();
  }

  /* ---------- events (delegated, so they survive a refresh) ---------- */
  root.addEventListener('click', function (e) {
    var t = e.target.closest('[data-period],[data-measure],[data-refresh]');
    if (!t) { return; }
    if (t.hasAttribute('data-refresh')) { refresh(true); return; }
    var card = t.closest('[data-chart]'); if (!card) { return; }
    if (t.hasAttribute('data-period')) { state.period = t.getAttribute('data-period'); }
    if (t.hasAttribute('data-measure')) { state.measure = t.getAttribute('data-measure'); }
    syncControls(card); renderChart(card);
  });
  root.addEventListener('input', function (e) {
    if (!e.target.hasAttribute('data-feed-search')) { return; }
    var q = e.target.value.trim().toLowerCase(), shown = 0;
    root.querySelectorAll('[data-feed-item]').forEach(function (li) { var ok = li.textContent.toLowerCase().indexOf(q) > -1; li.hidden = !ok; if (ok) { shown++; } });
    var none = root.querySelector('[data-feed-empty]'); if (none) { none.hidden = shown > 0; }
  });

  /* ---------- refresh without reloading the page ---------- */
  function refresh(manual) {
    if (busy) { return; }
    busy = true;
    var btn = root.querySelector('[data-refresh]'); if (btn) { btn.classList.add('is-spin'); }
    fetch(root.getAttribute('data-refresh-url') || location.href, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'dashboard-refresh' } })
      .then(function (r) {
        if (!r.ok || /\/login/.test(r.url)) { throw new Error('session'); }
        return r.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html'), fresh = doc.getElementById('dv');
        if (!fresh) { throw new Error('shape'); }
        root.innerHTML = fresh.innerHTML; lastUpdate = Date.now(); setup();
      })
      .catch(function (err) {
        var u = root.querySelector('[data-updated]');
        if (u) { u.textContent = err && err.message === 'session' ? 'Session expired — sign in again to keep the dashboard live' : 'Could not refresh — will try again'; }
        if (err && err.message === 'session') { clearInterval(timer); }
      })
      .then(function () { busy = false; var b = root.querySelector('[data-refresh]'); if (b) { b.classList.remove('is-spin'); } });
  }

  setup();
  setInterval(tick, 1000);
  timer = setInterval(function () { if (document.visibilityState === 'visible') { refresh(false); } }, 60000);
  document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'visible' && Date.now() - lastUpdate > 60000) { refresh(false); } });
  var rz; window.addEventListener('resize', function () { clearTimeout(rz); rz = setTimeout(function () { root.querySelectorAll('[data-chart]').forEach(renderChart); }, 180); });
})();
