CS.apps['qr-code'] = function (root) {
  var el = CS.el;
  var S = { kind: 'Link or text', text: 'https://cr3scix.com', ssid: '', pass: '', sec: 'WPA', hidden: false, phone: '', ecl: 'M', size: 512, fg: '#111111', bg: '#ffffff' };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'panel' });
  var fields = el('div'), canvas = el('canvas', { width: '512', height: '512', style: 'max-width:100%;height:auto;border:1px solid var(--line);border-radius:10px;background:#fff', role: 'img', 'aria-label': 'QR code preview' });
  var msg = el('div', { class: 'hint', 'aria-live': 'polite', style: 'margin-top:8px' });
  CS.add(form, 'What should it open?', CS.sel(['Link or text', 'Wi-Fi network', 'Phone number'], S.kind, function (v) { S.kind = v; drawFields(); draw(); }));
  form.appendChild(fields);
  CS.add(form, 'Error correction', CS.sel([['L', 'Low (smallest)'], ['M', 'Medium'], ['Q', 'High'], ['H', 'Highest (survives damage)']], S.ecl, function (v) { S.ecl = v; draw(); }));
  CS.add(form, 'Size in pixels', CS.sel([256, 512, 1024, 2048], S.size, function (v) { S.size = Number(v); draw(); }));
  var cols = el('div', { class: 'grid2' });
  var fg = CS.inp('color', S.fg, function (v) { S.fg = v; draw(); }), bg = CS.inp('color', S.bg, function (v) { S.bg = v; draw(); });
  cols.appendChild(el('div', {}, [el('label', { text: 'Dots' }), fg])); cols.appendChild(el('div', {}, [el('label', { text: 'Background' }), bg]));
  form.appendChild(cols);
  function drawFields() {
    fields.textContent = '';
    if (S.kind === 'Wi-Fi network') {
      CS.add(fields, 'Network name (SSID)', CS.inp('text', S.ssid, function (v) { S.ssid = v; draw(); }));
      CS.add(fields, 'Password', CS.inp('text', S.pass, function (v) { S.pass = v; draw(); }), 'Anyone who scans the code can read the password.');
      CS.add(fields, 'Security', CS.sel(['WPA', 'WEP', 'nopass'], S.sec, function (v) { S.sec = v; draw(); }));
    } else if (S.kind === 'Phone number') {
      CS.add(fields, 'Phone number with country code', CS.inp('tel', S.phone, function (v) { S.phone = v; draw(); }, '+91 98765 43210'));
    } else {
      CS.add(fields, 'Link or text', CS.area(S.text, function (v) { S.text = v; draw(); }, 3));
    }
  }
  function esc(s) { return s.replace(/([\;,:"])/g, '\\$1'); }
  function payload() {
    if (S.kind === 'Wi-Fi network') return S.ssid ? 'WIFI:T:' + S.sec + ';S:' + esc(S.ssid) + ';' + (S.sec !== 'nopass' ? 'P:' + esc(S.pass) + ';' : '') + ';' : '';
    if (S.kind === 'Phone number') { var d = S.phone.replace(/[^\d+]/g, ''); return d ? 'tel:' + d : ''; }
    return S.text.trim();
  }
  var qr = null;
  function draw() {
    msg.textContent = '';
    var data = payload(), ctx = canvas.getContext('2d');
    canvas.width = canvas.height = S.size;
    ctx.fillStyle = S.bg; ctx.fillRect(0, 0, S.size, S.size);
    if (!data) { qr = null; msg.textContent = 'Fill in the details to see your code.'; return; }
    try { qr = qrcode(0, S.ecl); qr.addData(data, 'Byte'); qr.make(); } catch (e) { qr = null; msg.textContent = 'That is too much data for one QR code. Shorten it or lower the error correction.'; return; }
    var n = qr.getModuleCount(), cell = S.size / (n + 8);
    ctx.fillStyle = S.fg;
    for (var r = 0; r < n; r++) for (var c = 0; c < n; c++) if (qr.isDark(r, c)) ctx.fillRect(Math.floor((c + 4) * cell), Math.floor((r + 4) * cell), Math.ceil(cell), Math.ceil(cell));
    msg.textContent = 'Test it with your phone camera before printing. Keep strong contrast between dots and background.';
  }
  function svg() {
    if (!qr) return null;
    var n = qr.getModuleCount(), d = '';
    for (var r = 0; r < n; r++) for (var c = 0; c < n; c++) if (qr.isDark(r, c)) d += 'M' + (c + 4) + ' ' + (r + 4) + 'h1v1h-1z';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + (n + 8) + ' ' + (n + 8) + '" shape-rendering="crispEdges"><rect width="100%" height="100%" fill="' + S.bg + '"/><path fill="' + S.fg + '" d="' + d + '"/></svg>';
  }
  right.appendChild(canvas); right.appendChild(msg);
  right.appendChild(el('div', { class: 'row' }, [
    el('button', { class: 'btn', type: 'button', text: 'Download PNG', onclick: function () { if (qr) canvas.toBlob(function (b) { CS.download('qr-code.png', b); }); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Download SVG', onclick: function () { var s = svg(); if (s) CS.download('qr-code.svg', new Blob([s], { type: 'image/svg+xml' })); } })
  ]));
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
  CS.load('qr-code', 'qrcode.js').then(function () { qrcode.stringToBytes = function (s) { return Array.prototype.slice.call(new TextEncoder().encode(s)); }; drawFields(); draw(); }, function () { msg.textContent = 'Could not load the QR library. Check your connection.'; });
};
