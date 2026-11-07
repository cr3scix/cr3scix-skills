CS.apps['social-post-checker'] = function (root) {
  var el = CS.el, P, chosen = { x: true, linkedin: true, instagram: true, threads: true, bluesky: true };
  var text = CS.area('', draw, 10, 'Paste your draft'), out = el('div', { class: 'panel' }), boxes = el('div', { class: 'grid2', style: 'margin-top:8px' });
  var form = el('div', { class: 'panel' }); CS.add(form, 'Your draft', text); form.appendChild(el('label', { text: 'Platforms' })); form.appendChild(boxes);
  form.appendChild(el('div', { class: 'row' }, [el('a', { class: 'btn ghost', href: '#/app/text-toolkit', text: 'Word count and case tools', style: 'text-decoration:none' })]));
  var URL_RE = /https?:\/\/\S+|www\.\S+/gi, TAG_RE = /(^|[^\w])#[\wÀ-￿]+/g;
  function xLen(t) { var urls = t.match(URL_RE) || [], rest = t.replace(URL_RE, ''), n = urls.length * 23; Array.from(rest).forEach(function (ch) { var c = ch.codePointAt(0); n += (c <= 4351 || (c >= 8192 && c <= 8205) || (c >= 8208 && c <= 8223) || (c >= 8242 && c <= 8247)) ? 1 : 2; }); return n; }
  CS.checkPost = function (t, key) {
    var p = P[key], low = t.toLowerCase(), r = { platform: p.name, length: key.indexOf('x') === 0 ? xLen(t) : Array.from(t.normalize('NFC')).length, limit: p.limit, errors: [], warnings: [] };
    if (r.length > p.limit) r.errors.push((r.length - p.limit) + ' characters over the ' + p.limit + ' limit');
    var tags = (t.match(TAG_RE) || []).map(function (s) { return s.trim().replace(/^[^#]*/, ''); });
    if (p.hashtags !== null && tags.length > p.hashtags) r.warnings.push(tags.length + ' hashtags; ' + p.hashtags + ' or fewer is safer here');
    if (key === 'mastodon') { var lc = tags.filter(function (g) { return g.length > 9 && g.slice(1) === g.slice(1).toLowerCase(); }); if (lc.length) r.warnings.push('Use CamelCase in long hashtags for screen readers: ' + lc.slice(0, 3).join(', ')); }
    if ((t.match(URL_RE) || []).length && !p.link_ok) r.warnings.push(key === 'instagram' || key === 'tiktok' ? 'Links in captions are not clickable here' : 'A link in the body tends to cut reach; try the first comment or reply');
    var first = t.trim().split('\n')[0]; if (first.length > p.preview) r.warnings.push('First line is ' + first.length + ' characters; about ' + p.preview + ' show before "more"');
    if (/[—–]/.test(t)) r.warnings.push('Contains an em or en dash, a common sign of AI-written text');
    var slop = window.CS_SLOP.filter(function (w) { return low.indexOf(w) > -1; }); if (slop.length) r.warnings.push('Stock phrases: ' + slop.join(', '));
    var bait = window.CS_BAIT.filter(function (w) { return low.indexOf(w) > -1; }); if (bait.length) r.warnings.push('Engagement bait some platforms demote: ' + bait.join(', '));
    if (/\{\{[^}]+\}\}|\[(your|insert)[^\]]*\]/i.test(t)) r.errors.push('Unfilled placeholder left in the draft');
    return r;
  };
  function draw() {
    out.textContent = '';
    if (!P) return;
    if (!text.value.trim()) { out.appendChild(el('div', { class: 'empty', text: 'Paste a draft to check it.' })); return; }
    Object.keys(P).filter(function (k) { return chosen[k]; }).forEach(function (k) {
      var r = CS.checkPost(text.value, k), state = r.errors.length ? 'fail' : (r.warnings.length ? 'partial' : 'pass');
      var pct = Math.min(100, r.length / r.limit * 100);
      out.appendChild(el('div', { class: 'check' }, [
        el('span', { class: 'pill ' + state, text: state === 'partial' ? 'check' : state === 'pass' ? 'ok' : 'fail' }), el('b', { text: r.platform }), el('span', { class: 'hint', text: r.length.toLocaleString() + ' / ' + r.limit.toLocaleString() }),
        el('div', { class: 'd' }, [el('div', { class: 'meter ' + (r.errors.length ? 'bad' : pct > 90 ? 'warn' : '') }, [el('i', { style: 'width:' + pct + '%' })])]),
        r.errors.concat(r.warnings).length ? el('div', { class: 'fix', text: r.errors.concat(r.warnings).join('. ') + '.' }) : null
      ]));
    });
  }
  CS.load('social-post-checker', 'platforms.js').then(function () {
    P = window.CS_PLATFORMS;
    Object.keys(P).forEach(function (k) {
      var c = el('input', { type: 'checkbox', id: 'pc-' + k }); c.checked = !!chosen[k];
      c.addEventListener('change', function () { chosen[k] = c.checked; draw(); });
      boxes.appendChild(el('label', { for: 'pc-' + k, style: 'display:flex;gap:6px;align-items:center;font-weight:400;margin:0' }, [c, P[k].name]));
    });
    draw();
  });
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
