CS.apps['seo-meta-preview'] = function (root) {
  var el = CS.el;
  var S = { title: 'Cr3scix | AI automation and web systems for small businesses', desc: 'We build AI process automation, fast websites and custom integrations that remove bottlenecks and help small teams grow.', url: 'https://example.com/services/', img: '' };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'stack' });
  var tMeter = el('div'), dMeter = el('div'), serp = el('div'), code = CS.out('');
  CS.add(form, 'Page title', CS.inp('text', S.title, function (v) { S.title = v; draw(); }));
  form.appendChild(tMeter);
  CS.add(form, 'Meta description', CS.area(S.desc, function (v) { S.desc = v; draw(); }, 4));
  form.appendChild(dMeter);
  CS.add(form, 'Page URL', CS.inp('text', S.url, function (v) { S.url = v; draw(); }));
  CS.add(form, 'Social image URL (optional)', CS.inp('text', '', function (v) { S.img = v; draw(); }), 'A 1200 by 630 image works well for link previews.');
  function meter(box, len, lo, hi, label) {
    box.textContent = '';
    var cls = len === 0 ? 'bad' : len < lo ? 'warn' : len <= hi ? '' : 'bad';
    var msg = len === 0 ? 'Empty.' : len < lo ? 'A little short. Aim for ' + lo + ' to ' + hi + '.' : len <= hi ? 'Good length.' : 'Too long. It will likely be cut off.';
    box.appendChild(el('div', { class: 'hint', text: len + ' characters. ' + msg }));
    box.appendChild(el('div', { class: 'meter ' + cls }, [el('i', { style: 'width:' + Math.min(100, len / hi * 100) + '%' })]));
  }
  function cut(s, n) { return s.length > n ? s.slice(0, n - 1).trimEnd() + '…' : s; }
  function attr(s) { return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }
  function draw() {
    meter(tMeter, S.title.length, 30, 60); meter(dMeter, S.desc.length, 70, 160);
    serp.textContent = '';
    var host = S.url.replace(/^https?:\/\//, '').replace(/\/$/, '').split('/').join(' › ');
    serp.appendChild(el('div', { class: 'serp' }, [el('div', { class: 'u', text: host || 'example.com' }), el('div', { class: 't', text: cut(S.title, 60) || 'Title' }), el('div', { class: 'd', text: cut(S.desc, 160) || 'Description' })]));
    var t = '<title>' + attr(S.title) + '</title>\n<meta name="description" content="' + attr(S.desc) + '">\n';
    if (S.url) t += '<link rel="canonical" href="' + attr(S.url) + '">\n';
    t += '<meta property="og:type" content="website">\n<meta property="og:title" content="' + attr(S.title) + '">\n<meta property="og:description" content="' + attr(S.desc) + '">\n';
    if (S.url) t += '<meta property="og:url" content="' + attr(S.url) + '">\n';
    if (S.img) t += '<meta property="og:image" content="' + attr(S.img) + '">\n';
    t += '<meta name="twitter:card" content="' + (S.img ? 'summary_large_image' : 'summary') + '">';
    code.set(t);
  }
  right.appendChild(el('div', { class: 'panel' }, [el('b', { text: 'How it may look in Google' }), el('div', { style: 'margin-top:10px' }, [serp]), el('p', { class: 'hint', text: 'Google decides what to show, so treat this as a guide. Cut-off points vary by device.' })]));
  right.appendChild(el('div', { class: 'panel' }, [el('b', { text: 'Tags to paste into your page head' }), el('div', { style: 'margin-top:10px' }, [code])]));
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
