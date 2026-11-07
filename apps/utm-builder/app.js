CS.apps['utm-builder'] = function (root) {
  var el = CS.el, S = { url: '', source: '', medium: '', campaign: '', term: '', content: '' };
  var form = el('div', { class: 'panel' }), out = CS.out(''), msg = el('div', { 'aria-live': 'polite', style: 'margin-top:8px' });
  function dl(id, vals) { var d = el('datalist', { id: id }); vals.forEach(function (v) { d.appendChild(el('option', { value: v })); }); return d; }
  function field(key, label, hint, list) { var i = CS.inp('text', '', function (v) { S[key] = v; draw(); }); if (list) i.setAttribute('list', list); CS.add(form, label, i, hint); }
  field('url', 'Page URL *', 'The page people should land on.');
  field('source', 'Source *', 'Where the link lives, for example newsletter or linkedin.', 'dl-src');
  field('medium', 'Medium *', 'The type of channel, for example email, social or cpc.', 'dl-med');
  field('campaign', 'Campaign *', 'A short name for the push, for example spring-launch.');
  field('term', 'Term (optional)', 'Paid keyword.');
  field('content', 'Content (optional)', 'Tells two links in the same place apart, for example header-button.');
  form.appendChild(dl('dl-src', ['google', 'newsletter', 'linkedin', 'instagram', 'facebook', 'youtube', 'whatsapp', 'x']));
  form.appendChild(dl('dl-med', ['email', 'social', 'cpc', 'referral', 'qr', 'video']));
  function clean(v) { return v.trim().toLowerCase().replace(/\s+/g, '-'); }
  function draw() {
    msg.textContent = '';
    if (!S.url.trim()) { out.set(''); return; }
    var u; try { u = new URL(/^https?:\/\//i.test(S.url.trim()) ? S.url.trim() : 'https://' + S.url.trim()); } catch (e) { out.set(''); msg.appendChild(el('div', { class: 'err', role: 'alert', text: 'That does not look like a web address yet.' })); return; }
    ['source', 'medium', 'campaign', 'term', 'content'].forEach(function (k) { if (S[k].trim()) u.searchParams.set('utm_' + k, clean(S[k])); else u.searchParams.delete('utm_' + k); });
    out.set(u.toString());
    var missing = ['source', 'medium', 'campaign'].filter(function (k) { return !S[k].trim(); });
    if (missing.length) msg.appendChild(el('div', { class: 'hint', text: 'Still needed for reporting to work well: ' + missing.join(', ') + '.' }));
    else msg.appendChild(el('div', { class: 'hint', text: 'Values are lowercased and spaces become dashes, so reports do not split "Email" and "email".' }));
  }
  var right = el('div', { class: 'panel' }); CS.add(right, 'Your tracked link', out); right.appendChild(msg);
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
