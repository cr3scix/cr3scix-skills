CS.apps['encode-decode'] = function (root) {
  var el = CS.el, inp = CS.area('', null, 8, 'Paste text, an encoded string or a JWT'), out = CS.out(''), msg = el('div', { 'aria-live': 'polite' });
  function say(t, bad) { msg.textContent = ''; if (t) msg.appendChild(el('div', { class: bad ? 'err' : 'hint', role: bad ? 'alert' : 'status', text: t })); }
  function b64e(s) { var b = new TextEncoder().encode(s), r = ''; b.forEach(function (c) { r += String.fromCharCode(c); }); return btoa(r); }
  function b64d(s) { s = s.trim().replace(/-/g, '+').replace(/_/g, '/'); while (s.length % 4) s += '='; var r = atob(s), a = new Uint8Array(r.length); for (var i = 0; i < r.length; i++) a[i] = r.charCodeAt(i); return new TextDecoder('utf-8', { fatal: true }).decode(a); }
  var esc = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
  var ops = {
    'Base64 encode': function (s) { return b64e(s); },
    'Base64 decode': function (s) { return b64d(s); },
    'URL encode': function (s) { return encodeURIComponent(s); },
    'URL decode': function (s) { return decodeURIComponent(s); },
    'HTML escape': function (s) { return s.replace(/[&<>"']/g, function (c) { return esc[c]; }); },
    'HTML unescape': function (s) { var d = new DOMParser().parseFromString(s, 'text/html'); return d.documentElement.textContent; },
    'Read JWT': function (s) {
      var p = s.trim().split('.'); if (p.length < 2) throw new Error('A JWT has three parts separated by dots.');
      var h = JSON.parse(b64d(p[0])), b = JSON.parse(b64d(p[1])), note = '';
      ['iat', 'nbf', 'exp'].forEach(function (k) { if (typeof b[k] === 'number') note += '\n' + k + ': ' + new Date(b[k] * 1000).toISOString() + (k === 'exp' && b[k] * 1000 < Date.now() ? '  (expired)' : ''); });
      return 'HEADER\n' + JSON.stringify(h, null, 2) + '\n\nPAYLOAD\n' + JSON.stringify(b, null, 2) + (note ? '\n\nTIMES' + note : '') + '\n\nThe signature is not checked here. Never trust a token because it decodes.';
    }
  };
  var bar = el('div', { class: 'seg', style: 'margin-top:12px' });
  Object.keys(ops).forEach(function (k) {
    bar.appendChild(el('button', { class: 'btn ghost', type: 'button', text: k, onclick: function () {
      try { out.set(ops[k](inp.value)); say(k + ' done.'); } catch (e) { out.set(''); say('That did not work: ' + (e.message || 'the input is not valid for this step.'), true); }
    } }));
  });
  var left = el('div', { class: 'panel' }); CS.add(left, 'Input', inp); left.appendChild(bar); left.appendChild(msg);
  var right = el('div', { class: 'panel' }); CS.add(right, 'Result', out);
  root.appendChild(el('div', { class: 'cols', style: 'grid-template-columns:1fr 1fr' }, [left, right]));
};
