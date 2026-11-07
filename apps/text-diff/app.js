CS.apps['text-diff'] = function (root) {
  var el = CS.el, a = CS.area('Our opening hours are 9 to 5.\nWe are closed on Sunday.\nCall us any time.', draw, 10), b = CS.area('Our opening hours are 9 to 6.\nWe are closed on Sunday.\nMessage us on WhatsApp any time.', draw, 10), out = el('div', { class: 'panel' });
  CS.diffLines = function (x, y) {
    var A = x.split('\n'), B = y.split('\n'), n = A.length, m = B.length;
    if (n * m > 4e6) return null;
    var L = []; for (var i = 0; i <= n; i++) { L.push(new Uint32Array(m + 1)); }
    for (i = n - 1; i >= 0; i--) for (var j = m - 1; j >= 0; j--) L[i][j] = A[i] === B[j] ? L[i + 1][j + 1] + 1 : Math.max(L[i + 1][j], L[i][j + 1]);
    var res = []; i = 0; j = 0;
    while (i < n && j < m) { if (A[i] === B[j]) { res.push([' ', A[i]]); i++; j++; } else if (L[i + 1][j] >= L[i][j + 1]) res.push(['-', A[i++]]); else res.push(['+', B[j++]]); }
    while (i < n) res.push(['-', A[i++]]); while (j < m) res.push(['+', B[j++]]);
    return res;
  };
  function draw() {
    out.textContent = '';
    var d = CS.diffLines(a.value, b.value);
    if (!d) { out.appendChild(el('div', { class: 'err', text: 'These texts are too long to compare here (about 2,000 lines each is the limit).' })); return; }
    var add = d.filter(function (r) { return r[0] === '+'; }).length, del = d.filter(function (r) { return r[0] === '-'; }).length;
    out.appendChild(el('b', { text: add + ' added, ' + del + ' removed' + (add + del ? '' : ' (the texts are the same)') }));
    var box = el('div', { style: 'margin-top:10px;font-family:ui-monospace,monospace;font-size:.85rem;border:1px solid var(--line);border-radius:10px;overflow:auto' });
    d.forEach(function (r) { box.appendChild(el('div', { style: 'white-space:pre-wrap;padding:2px 10px;' + (r[0] === '+' ? 'background:var(--okbg);color:var(--ok)' : r[0] === '-' ? 'background:var(--badbg);color:var(--bad)' : ''), text: r[0] + ' ' + r[1] })); });
    out.appendChild(box);
  }
  var left = el('div', { class: 'panel' }); CS.add(left, 'Original', a); CS.add(left, 'Changed', b);
  draw();
  root.appendChild(el('div', { class: 'cols', style: 'grid-template-columns:1fr 1fr' }, [left, out]));
};
