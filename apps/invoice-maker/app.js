CS.apps['invoice-maker'] = function (root) {
  var el = CS.el;
  var S = { type: 'Invoice', num: 'INV-001', date: new Date().toISOString().slice(0, 10), from: '', to: '', cur: '$', tax: 0, notes: '' };
  var items = [{ d: '', q: 1, p: 0 }];
  var form = el('div', { class: 'panel noprint' });
  var prev = el('div', { class: 'panel printable' });
  var list = el('div');

  function bind(key, label, ctl, hint) {
    CS.add(form, label, ctl, hint);
  }
  bind('type', 'Document type', CS.sel(['Invoice', 'Quote'], S.type, function (v) { S.type = v; if (S.num === 'INV-001' && v === 'Quote') { S.num = 'QUO-001'; } draw(); }));
  bind('num', 'Number', CS.inp('text', S.num, function (v) { S.num = v; draw(); }));
  bind('date', 'Date', CS.inp('date', S.date, function (v) { S.date = v; draw(); }));
  bind('from', 'From (your business)', CS.area('', function (v) { S.from = v; draw(); }, 3, 'Name, address, email'));
  bind('to', 'Bill to', CS.area('', function (v) { S.to = v; draw(); }, 3, 'Client name, address'));
  bind('cur', 'Currency symbol', CS.inp('text', S.cur, function (v) { S.cur = v; draw(); }));
  bind('tax', 'Tax (%)', CS.inp('number', 0, function (v) { S.tax = Number(v) || 0; draw(); }));
  form.appendChild(el('label', { text: 'Line items' }));
  form.appendChild(list);
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn ghost', type: 'button', text: 'Add line', onclick: function () { items.push({ d: '', q: 1, p: 0 }); drawItems(); draw(); } })]));
  bind('notes', 'Notes or payment details', CS.area('', function (v) { S.notes = v; draw(); }, 3));
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'Print or save as PDF', onclick: function () { window.print(); } })]));

  function drawItems() {
    list.textContent = '';
    items.forEach(function (it, i) {
      var d = CS.inp('text', it.d, function (v) { it.d = v; draw(); }, 'Description');
      var q = CS.inp('number', it.q, function (v) { it.q = Number(v) || 0; draw(); });
      var p = CS.inp('number', it.p, function (v) { it.p = Number(v) || 0; draw(); });
      q.min = '0'; p.min = '0'; q.step = 'any'; p.step = 'any';
      q.setAttribute('aria-label', 'Quantity'); p.setAttribute('aria-label', 'Unit price'); d.setAttribute('aria-label', 'Description');
      var rm = el('button', { class: 'btn ghost', type: 'button', text: 'Remove', 'aria-label': 'Remove line ' + (i + 1), onclick: function () { if (items.length > 1) { items.splice(i, 1); drawItems(); draw(); } } });
      list.appendChild(el('div', { class: 'irow' }, [d, q, p, rm]));
    });
  }
  function draw() {
    var sub = 0;
    items.forEach(function (it) { sub += it.q * it.p; });
    var tax = sub * S.tax / 100, total = sub + tax;
    prev.textContent = '';
    prev.appendChild(el('div', { style: 'display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap' }, [
      el('h2', { text: S.type, style: 'margin:0;font-size:1.8rem;color:var(--fg);text-transform:none;letter-spacing:0' }),
      el('div', { style: 'text-align:right' }, [el('b', { text: S.num }), el('div', { class: 'hint', text: S.date })])
    ]));
    prev.appendChild(el('div', { class: 'grid2', style: 'margin:18px 0' }, [
      el('div', {}, [el('div', { class: 'hint', text: 'From' }), el('div', { text: S.from || '—', style: 'white-space:pre-wrap' })]),
      el('div', {}, [el('div', { class: 'hint', text: 'Bill to' }), el('div', { text: S.to || '—', style: 'white-space:pre-wrap' })])
    ]));
    var t = el('table'), th = el('tr');
    ['Description', 'Qty', 'Price', 'Amount'].forEach(function (h, i) { th.appendChild(el('th', { text: h, style: i ? 'text-align:right' : '' })); });
    t.appendChild(el('thead', {}, [th]));
    var tb = el('tbody');
    items.forEach(function (it) {
      tb.appendChild(el('tr', {}, [el('td', { text: it.d || '—' }), el('td', { text: String(it.q), style: 'text-align:right' }), el('td', { text: CS.money(it.p, S.cur), style: 'text-align:right' }), el('td', { text: CS.money(it.q * it.p, S.cur), style: 'text-align:right' })]));
    });
    t.appendChild(tb);
    prev.appendChild(el('div', { class: 'tablewrap' }, [t]));
    var tot = el('div', { style: 'margin:14px 0 0 auto;max-width:260px' });
    [['Subtotal', sub], ['Tax (' + S.tax + '%)', tax]].forEach(function (r) { tot.appendChild(el('div', { style: 'display:flex;justify-content:space-between' }, [el('span', { text: r[0] }), el('span', { text: CS.money(r[1], S.cur) })])); });
    tot.appendChild(el('div', { style: 'display:flex;justify-content:space-between;font-weight:700;font-size:1.15rem;border-top:1px solid var(--line);margin-top:6px;padding-top:6px' }, [el('span', { text: 'Total' }), el('span', { text: CS.money(total, S.cur) })]));
    prev.appendChild(tot);
    if (S.notes) { prev.appendChild(el('p', { text: S.notes, style: 'white-space:pre-wrap;margin-top:18px' })); }
  }
  drawItems(); draw();
  root.appendChild(el('div', { class: 'cols', style: 'grid-template-columns:minmax(0,400px) minmax(0,1fr)' }, [form, prev]));
};
