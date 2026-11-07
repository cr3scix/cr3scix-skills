CS.apps['review-request'] = function (root) {
  var el = CS.el, S = { business: '', customer: '', link: '', channel: 'WhatsApp', job: '', from: '' };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'stack' });
  [['business', 'Your business name'], ['customer', 'Customer first name'], ['job', 'What you did for them (optional)', 'for example fixed the kitchen tap'], ['link', 'Review link', 'Your Google review link'], ['from', 'Signed by (optional)']].forEach(function (f) {
    CS.add(form, f[1], CS.inp('text', '', function (v) { S[f[0]] = v; draw(); }, f[2] || ''));
  });
  CS.add(form, 'Sending by', CS.sel(['WhatsApp', 'SMS', 'Email'], S.channel, function (v) { S.channel = v; draw(); }));
  form.appendChild(el('p', { class: 'hint', text: 'In Google Business Profile, choose "Ask for reviews" to copy your review link. Ask every happy customer the same way; offering rewards for reviews breaks Google\'s rules.' }));
  function build() { return CS.reviewMessages(S); }
  function draw() {
    right.textContent = '';
    build().forEach(function (m) {
      var box = CS.out(m.text);
      right.appendChild(el('div', { class: 'panel' }, [el('b', { text: m.label }), m.subject ? el('div', { class: 'hint', text: 'Subject: ' + m.subject }) : null, el('div', { style: 'margin-top:8px' }, [box])]));
    });
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
CS.reviewMessages = function (S) {
  var name = S.customer.trim() || 'there', biz = S.business.trim() || 'us', link = S.link.trim() || '{{review link}}', job = S.job.trim(), sig = S.from.trim() ? '\n' + S.from.trim() : '';
  var thanks = job ? 'Thanks again for choosing ' + biz + ' to ' + job.replace(/^to /i, '') + '.' : 'Thanks again for choosing ' + biz + '.';
  var out = [
    { label: 'Short and warm', text: 'Hi ' + name + ', ' + thanks + ' If you were happy with it, would you leave us a quick review? It really helps a small business like ours.\n' + link + sig },
    { label: 'Very short', text: 'Hi ' + name + ', it was great working with you. Would you mind sharing a quick review? ' + link + sig },
    { label: 'Asks for honest feedback', text: 'Hi ' + name + ', ' + thanks + ' We\'d love to hear how it went, good or bad. If you have a minute, you can leave a review here: ' + link + '\nAnd if anything wasn\'t right, just reply and we\'ll sort it out.' + sig }
  ];
  if (S.channel === 'SMS') out.forEach(function (m) { m.text = m.text.replace(/\n/g, ' '); });
  if (S.channel === 'Email') out.forEach(function (m) { m.subject = 'A quick favour from ' + biz; m.text = m.text.replace(/^Hi ([^,]+), /, 'Hi $1,\n\n'); });
  return out;
};
