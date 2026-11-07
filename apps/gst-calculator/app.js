CS.apps['gst-calculator'] = function (root) {
  var el = CS.el, S = { amount: 1000, rate: 18, mode: 'add', supply: 'intra' };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' });
  CS.add(form, 'Amount (₹)', CS.inp('number', S.amount, function (v) { S.amount = Number(v) || 0; draw(); }));
  var custom = CS.inp('number', S.rate, function (v) { S.rate = Number(v) || 0; draw(); }); custom.step = 'any';
  CS.add(form, 'GST rate', CS.sel([['18', '18% (standard)'], ['5', '5% (merit)'], ['40', '40% (luxury and sin goods)'], ['3', '3% (gold, silver)'], ['0.25', '0.25% (rough diamonds)'], ['0', '0% (exempt)'], ['custom', 'Other rate']], '18', function (v) { if (v === 'custom') { customBox.style.display = ''; S.rate = Number(custom.value) || 0; } else { customBox.style.display = 'none'; S.rate = Number(v); } draw(); }), 'Slabs since 22 September 2025. Check the rate for your item with your accountant or on the CBIC site.');
  var customBox = el('div', { style: 'display:none' }); CS.add(customBox, 'Rate (%)', custom); form.appendChild(customBox);
  CS.add(form, 'The amount is', CS.sel([['add', 'Before GST (add GST)'], ['remove', 'Including GST (take GST out)']], S.mode, function (v) { S.mode = v; draw(); }));
  CS.add(form, 'Sale is', CS.sel([['intra', 'Within the same state (CGST + SGST)'], ['inter', 'To another state (IGST)']], S.supply, function (v) { S.supply = v; draw(); }));
  function draw() {
    var base = S.mode === 'add' ? S.amount : S.amount / (1 + S.rate / 100), gst = base * S.rate / 100, r = {};
    r['Amount before GST'] = CS.money(base, '₹'); r['GST at ' + S.rate + '%'] = CS.money(gst, '₹');
    if (S.supply === 'intra') { r['CGST (' + S.rate / 2 + '%)'] = CS.money(gst / 2, '₹'); r['SGST (' + S.rate / 2 + '%)'] = CS.money(gst / 2, '₹'); }
    else r['IGST (' + S.rate + '%)'] = CS.money(gst, '₹');
    r['Total with GST'] = CS.money(base + gst, '₹');
    out.textContent = ''; out.appendChild(CS.value(r));
    out.appendChild(el('p', { class: 'hint', style: 'margin-top:12px', text: 'A calculator, not tax advice. Rounding on invoices follows your billing software.' }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
