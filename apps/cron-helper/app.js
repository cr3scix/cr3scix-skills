CS.apps['cron-helper'] = function (root) {
  var el = CS.el, inp = CS.inp('text', '30 9 * * 1-5', draw), out = el('div', { class: 'panel' });
  var form = el('div', { class: 'panel' });
  CS.add(form, 'Cron expression (minute hour day month weekday)', inp, 'Standard 5-field cron. Times are shown in your browser\'s time zone.');
  var presets = [['0 * * * *', 'Every hour'], ['*/15 * * * *', 'Every 15 minutes'], ['0 9 * * 1', 'Mondays at 9:00'], ['30 9 * * 1-5', 'Weekdays at 9:30'], ['0 0 1 * *', 'First of every month'], ['0 18 * * 5', 'Fridays at 18:00']];
  form.appendChild(el('div', { class: 'seg', style: 'margin-top:12px' }, presets.map(function (p) { return el('button', { class: 'btn ghost', type: 'button', text: p[1], onclick: function () { inp.value = p[0]; draw(); } }); })));
  var names = { mon: 1, tue: 2, wed: 3, thu: 4, fri: 5, sat: 6, sun: 0, jan: 1, feb: 2, mar: 3, apr: 4, may: 5, jun: 6, jul: 7, aug: 8, sep: 9, oct: 10, nov: 11, dec: 12 };
  var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  function field(s, lo, hi, label) {
    var set = {};
    s.toLowerCase().split(',').forEach(function (part) {
      var m = part.match(/^(\*|[a-z0-9]+(?:-[a-z0-9]+)?)(?:\/(\d+))?$/); if (!m) throw new Error('Could not read "' + part + '" in the ' + label + ' field.');
      var step = m[2] ? +m[2] : 1, a = lo, b = hi;
      if (step < 1) throw new Error('Step must be 1 or more.');
      if (m[1] !== '*') { var r = m[1].split('-').map(function (v) { return names[v] !== undefined ? names[v] : +v; }); a = r[0]; b = r.length > 1 ? r[1] : (m[2] ? hi : r[0]); }
      if (isNaN(a) || isNaN(b) || a < lo || b > hi || a > b) throw new Error('The ' + label + ' field must be between ' + lo + ' and ' + hi + '.');
      for (var v = a; v <= b; v += step) set[v] = true;
    });
    return set;
  }
  function list(set, fmt) { var k = Object.keys(set).map(Number).sort(function (a, b) { return a - b; }); return k.map(fmt).join(', '); }
  function draw() {
    out.textContent = '';
    var parts = inp.value.trim().split(/\s+/);
    try {
      if (parts.length !== 5) throw new Error('A cron expression has 5 parts separated by spaces.');
      var mi = field(parts[0], 0, 59, 'minute'), ho = field(parts[1], 0, 23, 'hour'), dm = field(parts[2], 1, 31, 'day of month'), mo = field(parts[3], 1, 12, 'month'), dw = field(parts[4], 0, 7, 'weekday'); if (dw[7]) { dw[0] = true; delete dw[7]; }
      var desc = [];
      desc.push(parts[0] === '*' ? 'every minute' : parts[1] === '*' ? 'at minute ' + list(mi, String) + ' of every hour' : 'at ' + list(ho, function (h) { return list(mi, function (m) { return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'); }); }));
      if (parts[2] !== '*') desc.push('on day ' + list(dm, String) + ' of the month');
      if (parts[4] !== '*') desc.push((parts[2] !== '*' ? 'or ' : '') + 'on ' + list(dw, function (d) { return days[d]; }));
      if (parts[3] !== '*') desc.push('in ' + list(mo, function (m) { return months[m]; }));
      var next = [], t = new Date(); t.setSeconds(0, 0); t.setMinutes(t.getMinutes() + 1);
      var domStar = parts[2] === '*', dowStar = parts[4] === '*';
      for (var i = 0; i < 527040 && next.length < 6; i++) {
        var dayOk = domStar && dowStar ? true : domStar ? dw[t.getDay()] : dowStar ? dm[t.getDate()] : (dm[t.getDate()] || dw[t.getDay()]);
        if (mi[t.getMinutes()] && ho[t.getHours()] && mo[t.getMonth() + 1] && dayOk) next.push(new Date(t));
        t.setMinutes(t.getMinutes() + 1);
      }
      out.appendChild(el('p', { text: 'Runs ' + desc.join(', ') + '.', style: 'font-size:1.1rem;margin-top:0' }));
      out.appendChild(el('b', { text: 'Next runs' }));
      out.appendChild(CS.value(next.length ? next.map(function (d) { return d.toLocaleString(undefined, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }) : ['None in the next year']));
    } catch (e) { out.appendChild(el('div', { class: 'err', role: 'alert', text: e.message })); }
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
