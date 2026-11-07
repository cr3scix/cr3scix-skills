CS.apps['id-generator'] = function (root) {
  var el = CS.el;
  var S = { kind: 'Password', len: 20, count: 5, up: true, low: true, dig: true, sym: true };
  var form = el('div', { class: 'panel' }), out = CS.out(''), note = el('div', { class: 'hint', style: 'margin-top:8px' });
  function rnd(n) { var lim = Math.floor(4294967296 / n) * n, a = new Uint32Array(1); do { crypto.getRandomValues(a); } while (a[0] >= lim); return a[0] % n; }
  function uuid() { if (crypto.randomUUID) return crypto.randomUUID(); var b = new Uint8Array(16); crypto.getRandomValues(b); b[6] = (b[6] & 15) | 64; b[8] = (b[8] & 63) | 128; var h = Array.prototype.map.call(b, function (x) { return ('0' + x.toString(16)).slice(-2); }).join(''); return h.replace(/(.{8})(.{4})(.{4})(.{4})(.{12})/, '$1-$2-$3-$4-$5'); }
  function pass() {
    var sets = []; if (S.low) sets.push('abcdefghijkmnopqrstuvwxyz'); if (S.up) sets.push('ABCDEFGHJKLMNPQRSTUVWXYZ'); if (S.dig) sets.push('23456789'); if (S.sym) sets.push('!@#$%^&*()-_=+[]{}?');
    if (!sets.length) return null;
    var all = sets.join(''), r = sets.map(function (s) { return s[rnd(s.length)]; });
    while (r.length < S.len) r.push(all[rnd(all.length)]);
    for (var i = r.length - 1; i > 0; i--) { var j = rnd(i + 1), t = r[i]; r[i] = r[j]; r[j] = t; }
    return r.join('');
  }
  function hex(n) { var b = new Uint8Array(n); crypto.getRandomValues(b); return Array.prototype.map.call(b, function (x) { return ('0' + x.toString(16)).slice(-2); }).join(''); }
  function gen() {
    var lines = [];
    for (var i = 0; i < S.count; i++) {
      var v = S.kind === 'UUID v4' ? uuid() : S.kind === 'Hex token' ? hex(Math.max(1, Math.round(S.len / 2))) : pass();
      if (v === null) { out.set(''); note.textContent = 'Turn on at least one character type.'; return; }
      lines.push(v);
    }
    out.set(lines.join('\n'));
    note.textContent = 'Generated in your browser with the secure random source. Nothing is sent anywhere.';
  }
  CS.add(form, 'Type', CS.sel(['Password', 'UUID v4', 'Hex token'], S.kind, function (v) { S.kind = v; gen(); }));
  CS.add(form, 'Length (passwords and tokens)', CS.inp('number', S.len, function (v) { S.len = Math.min(128, Math.max(4, Number(v) || 20)); gen(); }), '4 to 128. For passwords, 16 or more is a good habit.');
  CS.add(form, 'How many', CS.inp('number', S.count, function (v) { S.count = Math.min(50, Math.max(1, Number(v) || 1)); gen(); }));
  [['low', 'Lowercase'], ['up', 'Uppercase'], ['dig', 'Digits'], ['sym', 'Symbols']].forEach(function (o) {
    var c = el('input', { type: 'checkbox', id: 'cb-' + o[0] }); c.checked = S[o[0]]; c.addEventListener('change', function () { S[o[0]] = c.checked; gen(); });
    form.appendChild(el('label', { for: 'cb-' + o[0], style: 'display:flex;gap:8px;align-items:center;font-weight:400' }, [c, o[1]]));
  });
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'Generate again', onclick: gen })]));
  var right = el('div', { class: 'panel' }); CS.add(right, 'Results', out); right.appendChild(note);
  gen();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
