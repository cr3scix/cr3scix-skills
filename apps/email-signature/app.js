CS.apps['email-signature'] = function (root) {
  var el = CS.el;
  var S = { name: 'Asha Kumar', role: 'Founder', company: 'Example Studio', phone: '+91 98765 43210', email: 'asha@example.com', website: 'example.com', logo: '', color: '#0f766e' };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'panel' }), preview = el('div', { style: 'background:#fff;color:#111;padding:16px;border-radius:10px;border:1px solid var(--line);overflow:auto' }), code = CS.out('');
  [['name', 'Full name'], ['role', 'Role'], ['company', 'Company'], ['phone', 'Phone'], ['email', 'Email'], ['website', 'Website'], ['logo', 'Logo image URL (optional)']].forEach(function (f) {
    CS.add(form, f[1], CS.inp('text', S[f[0]], function (v) { S[f[0]] = v; draw(); }));
  });
  CS.add(form, 'Accent color', CS.inp('color', S.color, function (v) { S.color = v; draw(); }));
  function e(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
  function html() {
    var c = /^#[0-9a-f]{6}$/i.test(S.color) ? S.color : '#0f766e', web = S.website.trim(), href = web && !/^https?:\/\//i.test(web) ? 'https://' + web : web;
    var lines = [];
    if (S.phone.trim()) lines.push('<a href="tel:' + e(S.phone.replace(/[^\d+]/g, '')) + '" style="color:#333;text-decoration:none">' + e(S.phone) + '</a>');
    if (S.email.trim()) lines.push('<a href="mailto:' + e(S.email.trim()) + '" style="color:#333;text-decoration:none">' + e(S.email.trim()) + '</a>');
    if (web) lines.push('<a href="' + e(href) + '" style="color:' + c + ';text-decoration:none">' + e(web.replace(/^https?:\/\//, '')) + '</a>');
    var logo = /^https:\/\//i.test(S.logo.trim()) ? '<td style="padding-right:14px;vertical-align:top"><img src="' + e(S.logo.trim()) + '" alt="' + e(S.company) + '" width="64" style="display:block;border:0;max-width:64px"></td>' : '';
    return '<table cellpadding="0" cellspacing="0" border="0" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.5;color:#333"><tr>' + logo +
      '<td style="border-left:3px solid ' + c + ';padding-left:12px;vertical-align:top"><div style="font-size:15px;font-weight:bold;color:#111">' + e(S.name) + '</div>' +
      '<div>' + e([S.role, S.company].filter(function (x) { return x.trim(); }).join(', ')) + '</div><div>' + lines.join(' &nbsp;|&nbsp; ') + '</div></td></tr></table>';
  }
  function draw() { var h = html(); preview.innerHTML = h; code.set(h); }
  var copyRich = el('button', { class: 'btn', type: 'button', text: 'Copy signature', onclick: function () {
    var h = html();
    if (window.ClipboardItem && navigator.clipboard && navigator.clipboard.write) {
      navigator.clipboard.write([new ClipboardItem({ 'text/html': new Blob([h], { type: 'text/html' }), 'text/plain': new Blob([preview.innerText], { type: 'text/plain' }) })]).then(function () { copyRich.textContent = 'Copied'; setTimeout(function () { copyRich.textContent = 'Copy signature'; }, 1400); });
    } else { var r = document.createRange(); r.selectNodeContents(preview); var s = getSelection(); s.removeAllRanges(); s.addRange(r); copyRich.textContent = 'Press Ctrl+C'; }
  } });
  right.appendChild(el('b', { text: 'Preview' })); right.appendChild(el('div', { style: 'margin:10px 0' }, [preview]));
  right.appendChild(el('div', { class: 'row', style: 'margin:0 0 14px' }, [copyRich]));
  right.appendChild(el('p', { class: 'hint', text: 'Gmail: Settings, See all settings, Signature, then paste. Outlook: Settings, Mail, Compose and reply, then paste. The logo must be an https image link.' }));
  CS.add(right, 'HTML (for tools that take code)', code);
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
