CS.apps['csv-json'] = function (root) {
  var el = CS.el, inp = CS.area('name,city,orders\n"Lee, Ana",Chennai,4\nRavi,Pune,2', null, 12), out = CS.out(''), msg = el('div', { 'aria-live': 'polite', style: 'margin-top:8px' });
  function say(t, bad) { msg.textContent = ''; msg.appendChild(el('div', { class: bad ? 'err' : 'hint', role: bad ? 'alert' : 'status', text: t })); }
  CS.parseCsv = function (text, delim) {
    if (!delim) { var head = text.split('\n')[0]; delim = [',', ';', '\t', '|'].sort(function (a, b) { return head.split(b).length - head.split(a).length; })[0]; }
    var rows = [], row = [], f = '', q = false, i = 0;
    for (; i < text.length; i++) {
      var c = text[i];
      if (q) { if (c === '"') { if (text[i + 1] === '"') { f += '"'; i++; } else q = false; } else f += c; }
      else if (c === '"' && f === '') q = true;
      else if (c === delim) { row.push(f); f = ''; }
      else if (c === '\n' || c === '\r') { if (c === '\r' && text[i + 1] === '\n') i++; row.push(f); rows.push(row); row = []; f = ''; }
      else f += c;
    }
    if (f !== '' || row.length) { row.push(f); rows.push(row); }
    return { rows: rows.filter(function (r) { return r.length > 1 || r[0] !== ''; }), delim: delim };
  };
  function toJson() {
    var p = CS.parseCsv(inp.value), h = p.rows.shift() || [];
    if (!h.length) { say('Paste some CSV first.', true); return; }
    var data = p.rows.map(function (r) { var o = {}; h.forEach(function (k, i) { var v = r[i] === undefined ? '' : r[i]; o[k] = numbers.checked && v !== '' && !isNaN(v) && !/^0\d/.test(v) ? Number(v) : v; }); return o; });
    out.set(JSON.stringify(data, null, 2)); say(data.length + ' rows, ' + h.length + ' columns, delimiter "' + (p.delim === '\t' ? 'tab' : p.delim) + '".');
  }
  function toCsv() {
    var data; try { data = JSON.parse(inp.value); } catch (e) { say('That is not valid JSON.', true); return; }
    if (!Array.isArray(data) || !data.every(function (r) { return r && typeof r === 'object' && !Array.isArray(r); })) { say('Use a JSON array of objects, like [{"a":1}].', true); return; }
    var cols = []; data.forEach(function (r) { Object.keys(r).forEach(function (k) { if (cols.indexOf(k) < 0) cols.push(k); }); });
    var cell = function (v) { v = v === null || v === undefined ? '' : typeof v === 'object' ? JSON.stringify(v) : String(v); return /[",\n\r]/.test(v) || /^[=+\-@]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
    out.set([cols.map(cell).join(',')].concat(data.map(function (r) { return cols.map(function (c) { return cell(r[c]); }).join(','); })).join('\n'));
    say(data.length + ' rows, ' + cols.length + ' columns.');
  }
  var numbers = el('input', { type: 'checkbox', id: 'cj-num' }); numbers.checked = true;
  var left = el('div', { class: 'panel' }); CS.add(left, 'Input', inp);
  left.appendChild(el('label', { for: 'cj-num', style: 'display:flex;gap:8px;align-items:center;font-weight:400' }, [numbers, 'Turn number-looking values into numbers']));
  left.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'CSV to JSON', onclick: toJson }), el('button', { class: 'btn ghost', type: 'button', text: 'JSON to CSV', onclick: toCsv })]));
  left.appendChild(msg);
  var right = el('div', { class: 'panel' }); CS.add(right, 'Result', out);
  right.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn ghost', type: 'button', text: 'Download', onclick: function () { var t = out.querySelector('pre').textContent; if (t) CS.download(t.trim()[0] === '[' ? 'data.json' : 'data.csv', new Blob([t], { type: 'text/plain' })); } })]));
  root.appendChild(el('div', { class: 'cols', style: 'grid-template-columns:1fr 1fr' }, [left, right]));
};
