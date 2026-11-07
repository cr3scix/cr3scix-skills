CS.apps['contrast-checker'] = function (root) {
  var el = CS.el, S = { fg: '#1a1917', bg: '#f6f4ef' };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' }), sample = el('div', { style: 'padding:20px;border-radius:10px;border:1px solid var(--line);margin-bottom:14px' });
  function pair(key, label) { var c = CS.inp('color', S[key]), t = CS.inp('text', S[key]); c.addEventListener('input', function () { S[key] = c.value; t.value = c.value; draw(); }); t.addEventListener('input', function () { if (/^#?[0-9a-f]{3}([0-9a-f]{3})?$/i.test(t.value.trim())) { S[key] = norm(t.value); c.value = S[key]; draw(); } }); form.appendChild(el('label', { text: label })); form.appendChild(el('div', { class: 'grid2' }, [c, t])); }
  function norm(h) { h = h.trim().replace('#', ''); if (h.length === 3) h = h.split('').map(function (x) { return x + x; }).join(''); return '#' + h.toLowerCase(); }
  pair('fg', 'Text color'); pair('bg', 'Background color');
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn ghost', type: 'button', text: 'Swap', onclick: function () { var t = S.fg; S.fg = S.bg; S.bg = t; form.querySelectorAll('input').forEach(function (i, n) { i.value = n < 2 ? S.fg : S.bg; }); draw(); } })]));
  CS.contrast = function (a, b) {
    function lum(h) { return [1, 3, 5].map(function (i) { var c = parseInt(h.substr(i, 2), 16) / 255; return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); }).reduce(function (s, c, i) { return s + c * [0.2126, 0.7152, 0.0722][i]; }, 0); }
    var l1 = lum(a), l2 = lum(b); return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
  };
  function draw() {
    var r = CS.contrast(S.fg, S.bg), y = function (ok) { return ok ? 'Pass' : 'Fail'; };
    sample.style.background = S.bg; sample.style.color = S.fg;
    sample.textContent = ''; sample.appendChild(el('div', { text: 'Large heading text', style: 'font-size:24px;font-weight:700' })); sample.appendChild(el('div', { text: 'Normal body text should be easy to read for everyone, including people with low vision.' }));
    out.textContent = ''; out.appendChild(sample);
    out.appendChild(CS.value({ 'Contrast ratio': (Math.floor(r * 100) / 100) + ' : 1', 'AA normal text (4.5)': y(r >= 4.5), 'AA large text (3)': y(r >= 3), 'AAA normal text (7)': y(r >= 7), 'AAA large text (4.5)': y(r >= 4.5), 'Buttons, icons, borders (3)': y(r >= 3) }));
    out.appendChild(el('p', { class: 'hint', style: 'margin-top:12px', text: 'Large text means 24px regular or about 19px bold and up. Based on WCAG 2.2.' }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
