CS.apps['regex-tester'] = function (root) {
  var el = CS.el;
  var pat = CS.inp('text', '(\\w+)@(\\w+)\\.com', draw), flags = CS.inp('text', 'g', draw), txt = CS.area('Email ana@example.com or bob@test.com today.', draw, 8);
  flags.maxLength = 6; flags.style.maxWidth = '120px';
  var res = el('div');
  var form = el('div', { class: 'panel' });
  CS.add(form, 'Pattern', pat, 'Without the surrounding slashes.');
  CS.add(form, 'Flags', flags, 'g global, i ignore case, m multiline, s dot matches newline, u unicode.');
  CS.add(form, 'Test text', txt);
  var out = el('div', { class: 'panel' }, [res]);
  function draw() {
    res.textContent = '';
    if (!pat.value) { res.appendChild(el('div', { class: 'empty', text: 'Type a pattern to begin.' })); return; }
    var f = flags.value.replace(/[^dgimsuy]/g, ''); if (f.indexOf('g') < 0) f += 'g';
    var re;
    try { re = new RegExp(pat.value, f); } catch (e) { res.appendChild(el('div', { class: 'err', role: 'alert', text: 'Not a valid pattern: ' + e.message })); return; }
    var text = txt.value, ms = [], m, guard = 0;
    while ((m = re.exec(text)) !== null && guard++ < 500) { ms.push(m); if (m[0] === '') re.lastIndex++; }
    var hl = el('div', { style: 'white-space:pre-wrap;word-break:break-word;font-family:ui-monospace,monospace;font-size:.88rem;background:var(--code);border:1px solid var(--line);border-radius:10px;padding:12px' });
    var last = 0;
    ms.forEach(function (x) { if (x[0] === '') return; hl.appendChild(document.createTextNode(text.slice(last, x.index))); hl.appendChild(el('mark', { text: x[0] })); last = x.index + x[0].length; });
    hl.appendChild(document.createTextNode(text.slice(last)));
    res.appendChild(el('b', { text: ms.length + (ms.length === 1 ? ' match' : ' matches') + (guard >= 500 ? ' (showing the first 500)' : '') }));
    res.appendChild(el('div', { style: 'margin:10px 0' }, [hl]));
    if (ms.length) {
      var rows = ms.slice(0, 50).map(function (x, i) { var o = { '#': i + 1, Match: x[0], Position: x.index }; for (var g = 1; g < x.length; g++) o['Group ' + g] = x[g] === undefined ? '' : x[g]; return o; });
      res.appendChild(CS.value(rows));
    }
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
