CS.apps['margin-calculator'] = function (root) {
  var el = CS.el, S = { cost: 40, price: 100, fixed: 5000, target: 40 };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' });
  function num(key, label, hint) { CS.add(form, label, CS.inp('number', S[key], function (v) { S[key] = Number(v) || 0; draw(); }), hint); }
  num('cost', 'Cost per unit', 'What one unit costs you to make or buy, including delivery.');
  num('price', 'Selling price per unit');
  num('fixed', 'Fixed costs for the period (optional)', 'Rent, salaries, tools. Used for the break-even figure.');
  num('target', 'Target margin (%)', 'Used to find the price you would need.');
  var fmt = function (n) { return isFinite(n) ? CS.money(n) : '—'; };
  var pc = function (n) { return isFinite(n) ? (Math.round(n * 10) / 10) + '%' : '—'; };
  function draw() {
    var profit = S.price - S.cost;
    var margin = S.price ? profit / S.price * 100 : NaN;
    var markup = S.cost ? profit / S.cost * 100 : NaN;
    var needed = S.target < 100 ? S.cost / (1 - S.target / 100) : NaN;
    var be = profit > 0 && S.fixed > 0 ? Math.ceil(S.fixed / profit) : NaN;
    out.textContent = '';
    var res = { 'Profit per unit': fmt(profit), 'Margin': pc(margin), 'Markup on cost': pc(markup), ['Price for a ' + S.target + '% margin']: fmt(needed) };
    res['Units to break even'] = isFinite(be) ? be.toLocaleString() + (isFinite(be) ? ' (revenue ' + CS.money(be * S.price) + ')' : '') : (S.fixed > 0 ? 'Not reachable at this price' : '—');
    out.appendChild(CS.value(res));
    out.appendChild(el('p', { class: 'hint', style: 'margin-top:14px', text: 'Margin is profit as a share of the price. Markup is profit as a share of the cost. A 100% markup is only a 50% margin.' }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
