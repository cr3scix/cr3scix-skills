CS.apps['whatsapp-link'] = function (root) {
  var el = CS.el, S = { phone: '', msg: 'Hi! I found you online and would like to know more.' };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'panel' }), out = CS.out(''), note = el('div', { class: 'hint', 'aria-live': 'polite', style: 'margin-top:8px' }), test = el('a', { class: 'btn ghost', target: '_blank', rel: 'noopener noreferrer', text: 'Test the link', style: 'text-decoration:none;display:none' });
  CS.add(form, 'WhatsApp number with country code', CS.inp('tel', '', function (v) { S.phone = v; draw(); }, '+91 98765 43210'), 'Use the full international number. Leave out any leading 0 of the local number.');
  CS.add(form, 'Message people start with (optional)', CS.area(S.msg, function (v) { S.msg = v; draw(); }, 4));
  function draw() {
    var d = S.phone.replace(/\D/g, '');
    if (d.length < 8) { out.set(''); test.style.display = 'none'; note.textContent = 'Enter the number to get your link.'; return; }
    var link = 'https://wa.me/' + d + (S.msg.trim() ? '?text=' + encodeURIComponent(S.msg.trim()) : '');
    out.set(link); test.href = link; test.style.display = '';
    note.textContent = d.length > 15 ? 'That number looks too long. Check the country code.' : 'Put this on your website, bio or a QR code. It opens a chat with your message typed in.';
  }
  CS.add(right, 'Your link', out); right.appendChild(note);
  right.appendChild(el('div', { class: 'row' }, [test, el('a', { class: 'btn ghost', href: '#/app/qr-code', text: 'Make a QR code', style: 'text-decoration:none' })]));
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
