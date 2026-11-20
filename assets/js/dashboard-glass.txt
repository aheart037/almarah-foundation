/* Almarah Foundation — glass admin dashboard.
   Dependency-free. Everything the page needs is already in the HTML; this file adds the theme switch,
   the sliding dock marker, the "find a page" and notification dialogs, counters, the chart,
   the recent-donations filter and the live refresh. */
(function () {
  'use strict';
  var body = document.body;
  if (!body || !body.classList.contains('admin-glass')) { return; }
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.getElementById('gg');

  var ICONS = {
    moon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>',
    sun: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>',
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>',
    search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>'
  };

  /* ---------- theme (light / dark, remembered in this browser) ---------- */
  var THEME_KEY = 'almarah-admin-theme';
  function storedTheme() { try { return localStorage.getItem(THEME_KEY); } catch (e) { return null; } }
  function applyTheme(t) {
    body.setAttribute('data-gg-theme', t);
    var b = document.querySelector('[data-gg-theme-toggle]');
    if (b) {
      var label = t === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
      b.innerHTML = t === 'dark' ? ICONS.sun : ICONS.moon;
      b.setAttribute('aria-label', label); b.setAttribute('title', label);
    }
  }
  var initial = storedTheme() || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  applyTheme(initial);
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-gg-theme-toggle]');
    if (!t) { return; }
    var next = body.getAttribute('data-gg-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    try { localStorage.setItem(THEME_KEY, next); } catch (err) { /* private mode: keep for this visit only */ }
  });

  /* ---------- dock: sliding marker under the current destination ---------- */
  var dock = document.querySelector('.gg-dock');
  if (dock) {
    var marker = dock.querySelector('.gg-dock-indicator');
    var place = function () {
      var cur = dock.querySelector('[aria-current="page"]');
      if (!cur || !marker) { return; }
      marker.style.width = cur.offsetWidth + 'px'; marker.style.height = cur.offsetHeight + 'px';
      marker.style.transform = 'translate(' + cur.offsetLeft + 'px,' + cur.offsetTop + 'px)';
      marker.setAttribute('data-ready', 'true');
    };
    place();
    if (window.ResizeObserver) { var ro = new ResizeObserver(place); ro.observe(dock); dock.querySelectorAll('a').forEach(function (a) { ro.observe(a); }); }
    window.addEventListener('resize', place);
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(place); }
  }

  /* ---------- dialogs (native <dialog>) ---------- */
  function openDialog(title, build) {
    if (typeof HTMLDialogElement === 'undefined') { return; }
    var dlg = document.createElement('dialog');
    dlg.className = 'gg-dialog';
    var inner = document.createElement('section'); inner.className = 'gg-dialog-inner';
    var head = document.createElement('header');
    var h = document.createElement('h2'); h.textContent = title; dlg.setAttribute('aria-label', title);
    var x = document.createElement('button'); x.type = 'button'; x.className = 'gg-iconbtn'; x.setAttribute('aria-label', 'Close dialog'); x.innerHTML = ICONS.close;
    x.addEventListener('click', function () { dlg.close(); });
    head.appendChild(h); head.appendChild(x); inner.appendChild(head); dlg.appendChild(inner);
    build(inner, dlg);
    dlg.addEventListener('close', function () { dlg.remove(); });
    dlg.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); dlg.close(); } });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); } });
    body.appendChild(dlg); dlg.showModal();
    var first = dlg.querySelector('input'); if (first) { first.focus(); }
  }
  function row(href, title, small) {
    var a = document.createElement('a'); a.href = href;
    var d = document.createElement('div'); var s = document.createElement('strong'); s.textContent = title; d.appendChild(s);
    if (small) { var m = document.createElement('small'); m.textContent = small; d.appendChild(m); }
    a.appendChild(d); a.insertAdjacentHTML('beforeend', ICONS.arrow);
    return a;
  }

  /* "Find a page": searches the links already in the sidebar, so it always matches this user's permissions */
  function navEntries() {
    var out = [], seen = {}, group = '';
    document.querySelectorAll('.dash-nav .nav-group, .dash-nav a[href]').forEach(function (n) {
      if (n.classList.contains('nav-group')) { group = n.textContent.trim(); return; }
      var copy = n.cloneNode(true), badge = copy.querySelector('.nav-badge'); if (badge) { badge.remove(); }
      var label = copy.textContent.replace(/\s+/g, ' ').trim();
      var href = n.getAttribute('href');
      if (!label || !href || seen[href]) { return; }
      seen[href] = 1; out.push({ label: label, href: href, group: n.classList.contains('nav-sub') ? 'Reports' : group });
    });
    return out;
  }
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-gg-search]')) { return; }
    openDialog('Find a page', function (inner) {
      var entries = navEntries();
      var lab = document.createElement('label'); lab.className = 'gg-search';
      lab.insertAdjacentHTML('beforeend', ICONS.search);
      var input = document.createElement('input'); input.type = 'search'; input.placeholder = 'Find a page or report'; input.setAttribute('aria-label', 'Find an admin page');
      lab.appendChild(input);
      var list = document.createElement('div'); list.className = 'gg-quick-list';
      inner.appendChild(lab); inner.appendChild(list);
      function render() {
        var terms = input.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
        list.innerHTML = '';
        var n = 0;
        entries.forEach(function (it) {
          var hay = (it.label + ' ' + it.group).toLowerCase();
          if (terms.every(function (t) { return hay.indexOf(t) > -1; })) { list.appendChild(row(it.href, it.label, it.group)); n++; }
        });
        if (!n) { var p = document.createElement('p'); p.className = 'gg-empty-line'; p.setAttribute('role', 'status'); p.textContent = 'No matching pages.'; list.appendChild(p); }
      }
      input.addEventListener('input', render); render();
    });
  });

  /* Bell: lists the "needs your attention" items shown on the dashboard */
  function attentionItems() { return Array.prototype.slice.call(document.querySelectorAll('[data-attn] .gg-attn-item')); }
  function syncBell() {
    var dot = document.querySelector('[data-gg-bell] i');
    if (dot) { dot.hidden = attentionItems().length === 0; }
  }
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-gg-bell]')) { return; }
    openDialog('Needs your attention', function (inner) {
      var list = document.createElement('div'); list.className = 'gg-quick-list';
      var items = attentionItems();
      items.forEach(function (a) {
        var n = a.querySelector('b'), t = a.querySelector('span');
        list.appendChild(row(a.getAttribute('href'), (n ? n.textContent : '') + ' ' + (t ? t.textContent : ''), ''));
      });
      if (!items.length) { var p = document.createElement('p'); p.className = 'gg-empty-line'; p.textContent = 'You\u2019re all caught up.'; list.appendChild(p); }
      inner.appendChild(list);
    });
  });
  syncBell();

  if (!root) { return; }

  /* ================= dashboard content ================= */
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
    var target = parseInt(el.getAttribute('data-gg-count'), 10);
    var finalText = el.textContent;
    if (!isFinite(target) || reduce || target === 0) { return; }
    var money = el.getAttribute('data-kind') === 'money', sym = el.getAttribute('data-symbol') || '';
    var t0 = null, dur = 1200;
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
        ' \u00b7 ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
    var u = root.querySelector('[data-updated]');
    if (u) {
      var s = Math.round((Date.now() - lastUpdate) / 1000);
      u.textContent = (s < 10 ? 'Updated just now' : (s < 90 ? 'Updated ' + s + 's ago' : 'Updated ' + Math.round(s / 60) + ' min ago')) + ' \u00b7 refreshes automatically';
    }
  }

  /* ---------- chart ---------- */
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
    if (sub) { sub.textContent = (state.period === 'monthly' ? 'Monthly totals, last 12 months' : 'Daily totals, last 30 days') + ' \u00b7 ' + (key === 'm' ? data.currency : 'number of gifts'); }
    host.innerHTML = '';
    var vals = rows.map(function (r) { return r[key] || 0; });
    var total = vals.reduce(function (a, b) { return a + b; }, 0);
    if (!rows.length || total === 0) {
      host.innerHTML = '<div class="gg-chart-empty">No verified donations in this period yet.</div>';
      return;
    }
    var W = Math.max(280, host.clientWidth || 640), H = 280, L = key === 'm' ? 62 : 38, R = 14, T = 14, B = 30;
    var pw = W - L - R, ph = H - T - B, max = niceMax(Math.max.apply(null, vals));
    var s = svg('svg', { viewBox: '0 0 ' + W + ' ' + H, width: W, height: H, role: 'presentation' }, host);
    var defs = svg('defs', {}, s);
    var g1 = svg('linearGradient', { id: 'ggAreaGrad', x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
    svg('stop', { offset: '0%', style: 'stop-color:var(--gg-rose);stop-opacity:.36' }, g1);
    svg('stop', { offset: '100%', style: 'stop-color:var(--gg-rose);stop-opacity:0' }, g1);

    for (var i = 0; i <= 4; i++) {
      var y = T + ph - ph * i / 4, v = max * i / 4;
      svg('line', { x1: L, x2: W - R, y1: y, y2: y, 'class': 'gg-grid-line' }, s);
      var tx = svg('text', { x: L - 8, y: y + 3.5, 'text-anchor': 'end' }, s);
      tx.textContent = key === 'm' ? shortMinor(v, sym) : Math.round(v).toLocaleString('en-US');
    }
    var n = rows.length, step = pw / n;
    var xs = rows.map(function (r, i2) { return L + (state.period === 'monthly' ? step * (i2 + 0.5) : (n === 1 ? pw / 2 : pw * i2 / (n - 1))); });
    var every = state.period === 'monthly' ? (W < 480 ? 2 : 1) : Math.ceil(n / (W < 480 ? 5 : 8));
    var shown = [];
    rows.forEach(function (r, i3) { if (i3 % every === 0) { shown.push(i3); } });
    if (shown[shown.length - 1] !== n - 1) {
      if (shown.length && xs[n - 1] - xs[shown[shown.length - 1]] < 56) { shown.pop(); }
      shown.push(n - 1);
    }
    shown.forEach(function (i3) {
      var t = svg('text', { x: xs[i3], y: H - 8, 'text-anchor': i3 === n - 1 && n > 1 ? 'end' : 'middle' }, s); t.textContent = rows[i3].l;
    });
    var ys = vals.map(function (v2) { return T + ph - (v2 / max) * ph; });
    var maxIdx = vals.indexOf(Math.max.apply(null, vals));
    var cross = svg('line', { y1: T, y2: T + ph, 'class': 'gg-ch-cross' }, s), dot = svg('circle', { r: 5, 'class': 'gg-ch-dot' }, s);

    if (state.period === 'monthly') {
      var bw = Math.min(46, step * 0.62);
      rows.forEach(function (r, i4) {
        var h2 = Math.max(2, (vals[i4] / max) * ph);
        var b = svg('rect', { x: xs[i4] - bw / 2, y: T + ph - h2, width: bw, height: h2, rx: 6, 'class': 'gg-ch-bar' + (i4 === maxIdx ? ' hot' : ''), style: '--k:' + i4 }, s);
        if (reduce) { b.style.animation = 'none'; b.style.transform = 'none'; }
      });
    } else {
      var pts = xs.map(function (x, i5) { return [Math.round(x * 10) / 10, Math.round(ys[i5] * 10) / 10]; });
      var d = smooth(pts);
      var area = svg('path', { d: d + ' L' + pts[pts.length - 1][0] + ' ' + (T + ph) + ' L' + pts[0][0] + ' ' + (T + ph) + ' Z', fill: 'url(#ggAreaGrad)' }, s);
      var line = svg('path', { d: d, pathLength: 1, 'class': 'gg-ch-line', style: 'stroke-dasharray:1;stroke-dashoffset:' + (reduce ? 0 : 1) }, s);
      if (!reduce) {
        area.style.opacity = 0; area.style.transition = 'opacity .9s ease .5s';
        line.getBoundingClientRect();
        requestAnimationFrame(function () { line.style.transition = 'stroke-dashoffset 1.4s ease'; line.style.strokeDashoffset = 0; area.style.opacity = 1; });
      }
    }

    var tip = document.createElement('div'); tip.className = 'gg-tip'; host.appendChild(tip);
    var hit = svg('rect', { x: L, y: T, width: pw, height: ph, fill: 'transparent' }, s);
    function show(clientX) {
      var rect = s.getBoundingClientRect(), x = (clientX - rect.left) * (W / rect.width), idx = 0, best = 1e9;
      xs.forEach(function (px, i6) { var dd = Math.abs(px - x); if (dd < best) { best = dd; idx = i6; } });
      var r = rows[idx];
      cross.setAttribute('x1', xs[idx]); cross.setAttribute('x2', xs[idx]); cross.setAttribute('class', 'gg-ch-cross on');
      if (state.period === 'daily') { dot.setAttribute('cx', xs[idx]); dot.setAttribute('cy', ys[idx]); dot.setAttribute('class', 'gg-ch-dot on'); }
      tip.innerHTML = '<b></b><span></span>';
      tip.firstChild.textContent = key === 'm' ? shortMinor(r.m, sym) : r.c.toLocaleString('en-US') + ' gift' + (r.c === 1 ? '' : 's');
      tip.lastChild.textContent = r.l + ' \u00b7 ' + (key === 'm' ? r.c.toLocaleString('en-US') + ' gift' + (r.c === 1 ? '' : 's') : shortMinor(r.m, sym));
      tip.style.left = (xs[idx] / W * rect.width) + 'px'; tip.style.top = (Math.max(ys[idx], T + 30) / H * rect.height) + 'px'; tip.className = 'gg-tip on';
    }
    function hide() { cross.setAttribute('class', 'gg-ch-cross'); dot.setAttribute('class', 'gg-ch-dot'); tip.className = 'gg-tip'; }
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

  /* ---------- recent donations: text search + status filter ---------- */
  function applyFeedFilter() {
    var qEl = root.querySelector('[data-feed-search]'), sEl = root.querySelector('[data-feed-status]');
    if (!qEl && !sEl) { return; }
    var q = qEl ? qEl.value.trim().toLowerCase() : '', st = sEl ? sEl.value : '', shown = 0;
    root.querySelectorAll('[data-feed-item]').forEach(function (r) {
      var ok = (!q || r.textContent.toLowerCase().indexOf(q) > -1) && (!st || r.getAttribute('data-status') === st);
      r.hidden = !ok; if (ok) { shown++; }
    });
    var none = root.querySelector('[data-feed-empty]'); if (none) { none.hidden = shown > 0; }
  }

  /* ---------- per-render setup ---------- */
  function setup() {
    root.querySelectorAll('.gg-num').forEach(countUp);
    root.querySelectorAll('[data-chart]').forEach(function (card) { syncControls(card); renderChart(card); });
    tick(); syncBell();
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
  root.addEventListener('input', function (e) { if (e.target.hasAttribute('data-feed-search')) { applyFeedFilter(); } });
  root.addEventListener('change', function (e) { if (e.target.hasAttribute('data-feed-status')) { applyFeedFilter(); } });

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
        var doc = new DOMParser().parseFromString(html, 'text/html'), fresh = doc.getElementById('gg');
        if (!fresh) { throw new Error('shape'); }
        root.innerHTML = fresh.innerHTML; lastUpdate = Date.now(); setup();
      })
      .catch(function (err) {
        var u = root.querySelector('[data-updated]');
        if (u) { u.textContent = err && err.message === 'session' ? 'Session expired \u2014 sign in again to keep the dashboard live' : 'Could not refresh \u2014 will try again'; }
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
