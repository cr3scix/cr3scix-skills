CS.apps['loan-calculator'] = function (root) {
  var el = CS.el, S = { p: 500000, rate: 10.5, months: 60, cur: '₹' };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' });
  CS.add(form, 'Loan amount', CS.inp('number', S.p, function (v) { S.p = Math.max(0, Number(v) || 0); draw(); }));
  CS.add(form, 'Interest rate per year (%)', CS.inp('number', S.rate, function (v) { S.rate = Math.max(0, Number(v) || 0); draw(); }));
  CS.add(form, 'Term in months', CS.inp('number', S.months, function (v) { S.months = Math.max(1, Math.min(600, Math.round(Number(v) || 1))); draw(); }));
  CS.add(form, 'Currency symbol', CS.inp('text', S.cur, function (v) { S.cur = v; draw(); }));
  function draw() {
    var r = S.rate / 1200, n = S.months, emi = r ? S.p * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1) : S.p / n;
    var bal = S.p, years = [], y = null;
    for (var m = 1; m <= n; m++) {
      var interest = bal * r, princ = emi - interest; bal = Math.max(0, bal - princ);
      if (!y || (m - 1) % 12 === 0) { y = { Year: Math.ceil(m / 12), 'Principal paid': 0, 'Interest paid': 0, 'Balance left': 0 }; years.push(y); }
      y['Principal paid'] += princ; y['Interest paid'] += interest; y['Balance left'] = bal;
    }
    years.forEach(function (row) { ['Principal paid', 'Interest paid', 'Balance left'].forEach(function (k) { row[k] = CS.money(row[k], S.cur); }); });
    out.textContent = '';
    out.appendChild(CS.value({ 'Monthly instalment (EMI)': CS.money(emi, S.cur), 'Total interest': CS.money(emi * n - S.p, S.cur), 'Total paid': CS.money(emi * n, S.cur) }));
    out.appendChild(el('h2', { text: 'Year by year', style: 'margin:18px 0 8px' }));
    out.appendChild(CS.value(years));
    out.appendChild(el('p', { class: 'hint', style: 'margin-top:12px', text: 'Uses a fixed rate and equal monthly payments. Lenders may add fees or round differently.' }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
