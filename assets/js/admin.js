/* Admin panel behaviours: resource forms, drag reorder, HTML editor, menu editor. */
(function () {
  'use strict';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  // --- slug from title --------------------------------------------------
  document.querySelectorAll('[data-slug-from]').forEach(function (slugInput) {
    var source = document.getElementById(slugInput.getAttribute('data-slug-from'));
    if (!source) return;
    var touched = slugInput.value !== '';
    slugInput.addEventListener('input', function () { touched = slugInput.value !== ''; });
    source.addEventListener('input', function () {
      if (touched) return;
      slugInput.value = source.value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 150);
    });
  });

  // --- character counters ----------------------------------------------
  document.querySelectorAll('[data-counter]').forEach(function (el) {
    var limit = parseInt(el.getAttribute('data-counter'), 10) || 0;
    var out = document.createElement('div');
    out.className = 'rs-counter';
    el.insertAdjacentElement('afterend', out);
    var update = function () {
      var n = el.value.length;
      out.textContent = n + ' / ' + limit + ' characters' + (n > limit ? ' — a bit long for search results' : '');
      out.classList.toggle('over', n > limit);
    };
    el.addEventListener('input', update); update();
  });

  // --- image preview ----------------------------------------------------
  document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var img = input.parentElement.querySelector('.rs-preview-new');
      var file = input.files && input.files[0];
      if (!img || !file || !/^image\//.test(file.type)) { if (img) img.hidden = true; return; }
      img.src = URL.createObjectURL(file); img.hidden = false;
    });
  });

  // --- colour picker sync ------------------------------------------------
  document.querySelectorAll('.rs-color').forEach(function (wrap) {
    var pick = wrap.querySelector('input[type=color]'), text = wrap.querySelector('input[type=text]');
    pick.addEventListener('input', function () { text.value = pick.value; });
    text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) pick.value = text.value; });
  });

  // --- minimal HTML editor ------------------------------------------------
  document.querySelectorAll('[data-editor]').forEach(function (ed) {
    var area = ed.querySelector('.rs-editor-area'), src = ed.querySelector('.rs-editor-source');
    var form = ed.closest('form');
    var sync = function () { if (!ed.classList.contains('is-source')) src.value = area.innerHTML; };
    area.addEventListener('input', sync);
    ed.querySelectorAll('[data-cmd]').forEach(function (btn) {
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
      btn.addEventListener('click', function () {
        var cmd = btn.getAttribute('data-cmd'), arg = btn.getAttribute('data-arg') || null;
        if (ed.classList.contains('is-source')) return;
        area.focus();
        if (cmd === 'createLink') {
          var url = window.prompt('Link address (e.g. /donate or https://…)');
          if (!url) return;
          document.execCommand('createLink', false, url);
        } else {
          document.execCommand(cmd, false, arg);
        }
        sync();
      });
    });
    var toggle = ed.querySelector('[data-toggle-source]');
    toggle.addEventListener('click', function () {
      if (ed.classList.contains('is-source')) {
        area.innerHTML = src.value; ed.classList.remove('is-source'); toggle.textContent = 'HTML';
      } else {
        src.value = area.innerHTML; ed.classList.add('is-source'); toggle.textContent = 'Visual';
      }
    });
    area.addEventListener('paste', function (e) {
      // Paste as plain text so Word/Google Docs styling does not leak in.
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      if (!text) return;
      e.preventDefault();
      document.execCommand('insertText', false, text);
    });
    if (form) form.addEventListener('submit', sync);
  });

  // --- drag-to-reorder tables ------------------------------------------
  document.querySelectorAll('table[data-reorder-url]').forEach(function (table) {
    var tbody = table.tBodies[0], dragging = null;
    table.querySelectorAll('.rs-handle').forEach(function (h) {
      h.addEventListener('dragstart', function (e) {
        dragging = h.closest('tr'); dragging.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragging.dataset.id);
      });
      h.addEventListener('dragend', function () {
        if (dragging) dragging.classList.remove('is-dragging'); dragging = null;
        tbody.querySelectorAll('tr').forEach(function (r) { r.classList.remove('is-drop-target'); });
      });
    });
    tbody.addEventListener('dragover', function (e) {
      if (!dragging) return;
      e.preventDefault();
      var row = e.target.closest('tr'); if (!row || row === dragging) return;
      tbody.querySelectorAll('tr').forEach(function (r) { r.classList.remove('is-drop-target'); });
      row.classList.add('is-drop-target');
    });
    tbody.addEventListener('drop', function (e) {
      if (!dragging) return;
      e.preventDefault();
      var row = e.target.closest('tr'); if (!row || row === dragging) return;
      var rect = row.getBoundingClientRect();
      if (e.clientY < rect.top + rect.height / 2) tbody.insertBefore(dragging, row); else tbody.insertBefore(dragging, row.nextSibling);
      var ids = Array.prototype.map.call(tbody.querySelectorAll('tr'), function (r) { return r.dataset.id; });
      var body = new URLSearchParams(); body.append('_token', csrf); ids.forEach(function (id) { body.append('ids[]', id); });
      fetch(table.getAttribute('data-reorder-url'), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) { if (!j.ok) window.alert('Could not save the new order.'); })
        .catch(function () { window.alert('Could not save the new order.'); });
    });
  });

  // --- navigation menu editor ------------------------------------------
  var menuEditor = document.querySelector('[data-menu-editor]');
  if (menuEditor) {
    var hidden = document.getElementById(menuEditor.getAttribute('data-menu-editor'));
    var tree = []; try { tree = JSON.parse(hidden.value || '[]'); } catch (e) { tree = []; }
    var maxDepth = parseInt(menuEditor.getAttribute('data-max-depth') || '2', 10);
    var render = function () {
      menuEditor.innerHTML = '';
      menuEditor.appendChild(renderList(tree, 1));
      hidden.value = JSON.stringify(tree);
    };
    var renderList = function (items, depth) {
      var ul = document.createElement('ul');
      items.forEach(function (item, i) {
        var li = document.createElement('li');
        li.innerHTML =
          '<div class="mi-row">' +
            '<span class="mi-handle" title="Level ' + depth + '">≡</span>' +
            '<input class="input" type="text" placeholder="Label" value="' + esc(item.label || '') + '" data-k="label">' +
            '<input class="input mono" type="text" placeholder="page-address or https://…" value="' + esc(item.url || '') + '" data-k="url">' +
            '<div class="mi-actions">' +
              '<button type="button" data-a="up" title="Move up">↑</button>' +
              '<button type="button" data-a="down" title="Move down">↓</button>' +
              (depth < maxDepth ? '<button type="button" data-a="child" title="Add sub-item">＋</button>' : '') +
              '<button type="button" data-a="del" title="Remove">✕</button>' +
            '</div>' +
          '</div>' +
          '<div class="mi-opts">' +
            '<label><input type="checkbox" data-k="newtab"' + (item.newtab ? ' checked' : '') + '> Open in new tab</label>' +
          '</div>';
        li.querySelectorAll('[data-k]').forEach(function (inp) {
          inp.addEventListener('input', function () {
            var v = inp.type === 'checkbox' ? inp.checked : inp.value;
            if (inp.dataset.k === 'url' && typeof v === 'string' && v.charAt(0) === '/' && v.length > 1) { v = v.replace(/^\/+/, ''); }
            item[inp.dataset.k] = v;
            hidden.value = JSON.stringify(tree);
          });
        });
        li.querySelectorAll('[data-a]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var a = btn.dataset.a;
            if (a === 'up' && i > 0) { items.splice(i - 1, 0, items.splice(i, 1)[0]); }
            if (a === 'down' && i < items.length - 1) { items.splice(i + 1, 0, items.splice(i, 1)[0]); }
            if (a === 'del') { if (window.confirm('Remove "' + (item.label || 'this item') + '" and its sub-items?')) items.splice(i, 1); }
            if (a === 'child') { item.children = item.children || []; item.children.push({ label: '', url: '', children: [] }); }
            render();
          });
        });
        if (item.children && item.children.length && depth < maxDepth) li.appendChild(renderList(item.children, depth + 1));
        ul.appendChild(li);
      });
      return ul;
    };
    var esc = function (s) { return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); };
    var addBtn = document.querySelector('[data-menu-add]');
    if (addBtn) addBtn.addEventListener('click', function () { tree.push({ label: '', url: '', children: [] }); render(); });
    render();
  }
})();
(function () {
  var t = document.querySelector('[data-side-toggle]');
  var side = document.getElementById('adminSide');
  var backdrop = document.querySelector('[data-side-close]');
  if (!t || !side || !backdrop) return;
  function close() {
    side.classList.remove('is-open');
    backdrop.hidden = true;
    t.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('admin-nav-open');
    t.focus();
  }
  t.addEventListener('click', function () {
    if (side.classList.contains('is-open')) { close(); return; }
    side.classList.add('is-open');
    backdrop.hidden = false;
    t.setAttribute('aria-expanded', 'true');
    document.body.classList.add('admin-nav-open');
    var first = side.querySelector('a');
    if (first) first.focus();
  });
  backdrop.addEventListener('click', close);
  document.addEventListener('keydown', function (event) {
    if (!side.classList.contains('is-open')) return;
    if (event.key === 'Escape') { close(); return; }
    if (event.key !== 'Tab') return;
    var items = Array.prototype.slice.call(side.querySelectorAll('a, summary')).filter(function (el) { return el.getClientRects().length > 0; });
    if (!items.length) return;
    if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items[items.length - 1].focus(); }
    else if (!event.shiftKey && document.activeElement === items[items.length - 1]) { event.preventDefault(); items[0].focus(); }
  });
  window.matchMedia('(min-width: 1025px)').addEventListener('change', function (event) { if (event.matches && side.classList.contains('is-open')) close(); });
})();

/* Finance desk: show only the date inputs relevant to the chosen period. */
(function () {
  var sel = document.querySelector('[data-toggle-period]');
  if (!sel) return;
  function sync() {
    document.querySelectorAll('[data-period]').forEach(function (el) {
      el.hidden = el.getAttribute('data-period') !== sel.value;
    });
  }
  sel.addEventListener('change', sync); sync();
})();

/* ---------- Reports: feedback while the PDF is being prepared ---------- */
(function () {
  var btn = document.getElementById("pdfExport");
  if (!btn) { return; }
  btn.addEventListener("click", function () {
    btn.classList.add("is-busy");
    window.setTimeout(function () { btn.classList.remove("is-busy"); }, 4000);
  });
})();

/* Local search for the existing recent-transaction table; never changes server filters. */
(function () {
  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    var id = input.getAttribute('data-table-search');
    var table = document.getElementById(id);
    var empty = document.querySelector('[data-search-empty="' + id + '"]');
    if (!table) return;
    input.addEventListener('input', function () {
      var query = input.value.trim().toLocaleLowerCase();
      var shown = 0;
      table.querySelectorAll('tbody tr').forEach(function (row) {
        var match = row.textContent.toLocaleLowerCase().includes(query);
        row.hidden = !match;
        if (match) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
    });
  });
})();
