CS.apps['carousel-maker'] = function (root) {
  var el = CS.el;
  var S = { text: '3 signs your website is losing customers\nAnd what to fix first\n\nIt takes too long to load\nMost visitors leave if a page takes more than a few seconds.\n\nThe phone number is hard to find\nPut it at the top of every page, and make it tap-to-call.\n\nThere is no clear next step\nOne button. One action. Book, call or message.\n\nWant a free check?\nSend us a message and we will look at your site.', size: '1080x1350', bg: '#111111', fg: '#f5f5f5', accent: '#2dd4bf', handle: '@yourbrand', numbers: true };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' }), grid = el('div', { class: 'tiles', style: 'grid-template-columns:repeat(auto-fill,minmax(150px,1fr))' });
  CS.add(form, 'Slides', CS.area(S.text, function (v) { S.text = v; draw(); }, 14), 'Leave a blank line between slides. First line of each slide is the headline, the rest is the body. Wrap words in *stars* to color them.');
  CS.add(form, 'Size', CS.sel([['1080x1350', 'Portrait 1080 x 1350 (Instagram, LinkedIn)'], ['1080x1080', 'Square 1080 x 1080']], S.size, function (v) { S.size = v; draw(); }));
  var g = el('div', { class: 'grid2' });
  [['bg', 'Background'], ['fg', 'Text'], ['accent', 'Accent']].forEach(function (c) { g.appendChild(el('div', {}, [el('label', { text: c[1] }), CS.inp('color', S[c[0]], function (v) { S[c[0]] = v; draw(); })])); });
  form.appendChild(g);
  CS.add(form, 'Handle or brand name', CS.inp('text', S.handle, function (v) { S.handle = v; draw(); }));
  var canvases = [];
  function slides() { return S.text.split(/\n\s*\n/).map(function (b) { var l = b.trim().split('\n'); return { head: l[0] || '', body: l.slice(1).join(' ').trim() }; }).filter(function (s) { return s.head; }).slice(0, 20); }
  function wrap(ctx, text, max) { var words = text.split(/\s+/), lines = [], line = ''; words.forEach(function (w) { var t = line ? line + ' ' + w : w; if (ctx.measureText(t.replace(/\*/g, '')).width > max && line) { lines.push(line); line = w; } else line = t; }); if (line) lines.push(line); return lines; }
  function paint(ctx, line, x, y, base, accent) {
    var on = false; line.split(/(\*)/).forEach(function (part) { if (part === '*') { on = !on; return; } ctx.fillStyle = on ? accent : base; ctx.fillText(part, x, y); x += ctx.measureText(part).width; });
  }
  function render(c, s, i, n) {
    var p = S.size.split('x'), W = +p[0], H = +p[1], x = c.getContext('2d'), pad = 96;
    c.width = W; c.height = H;
    x.fillStyle = S.bg; x.fillRect(0, 0, W, H);
    x.fillStyle = S.accent; x.fillRect(pad, pad, 72, 10);
    x.textBaseline = 'top';
    var cover = i === 0, hs = cover ? 96 : 76;
    x.font = '700 ' + hs + 'px system-ui, -apple-system, Segoe UI, sans-serif';
    var hl = wrap(x, s.head, W - pad * 2), y = cover ? H * 0.28 : pad + 90;
    hl.forEach(function (l) { paint(x, l, pad, y, S.fg, S.accent); y += hs * 1.15; });
    if (s.body) {
      y += 30; x.font = '400 40px system-ui, -apple-system, Segoe UI, sans-serif';
      wrap(x, s.body, W - pad * 2).forEach(function (l) { paint(x, l, pad, y, S.fg, S.accent); y += 56; });
    }
    if (y > H - 150) { x.fillStyle = '#ff4d4d'; x.font = '700 28px system-ui, sans-serif'; x.fillText('Too much text for this slide', pad, H - 200); }
    x.font = '500 28px ui-monospace, Menlo, monospace'; x.fillStyle = S.fg; x.globalAlpha = 0.7;
    x.fillText(S.handle, pad, H - pad - 28);
    var num = (i + 1) + ' / ' + n; x.fillText(num, W - pad - x.measureText(num).width, H - pad - 28);
    x.globalAlpha = 1;
  }
  function draw() {
    grid.textContent = ''; canvases = [];
    var list = slides();
    list.forEach(function (s, i) { var c = el('canvas', { role: 'img', 'aria-label': 'Slide ' + (i + 1) + ': ' + s.head.replace(/\*/g, ''), style: 'width:100%;height:auto;border-radius:8px;border:1px solid var(--line)' }); render(c, s, i, list.length); canvases.push(c); grid.appendChild(c); });
    if (!list.length) grid.appendChild(el('div', { class: 'empty', text: 'Write at least one slide.' }));
  }
  out.appendChild(grid);
  out.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'Download all slides', onclick: function () {
    canvases.forEach(function (c, i) { setTimeout(function () { c.toBlob(function (b) { CS.download('slide-' + String(i + 1).padStart(2, '0') + '.png', b); }); }, i * 350); });
  } })]));
  out.appendChild(el('p', { class: 'hint', text: 'Your browser may ask once to allow several downloads. For LinkedIn, combine the images into a PDF with the PDF tool or your phone.' }));
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
