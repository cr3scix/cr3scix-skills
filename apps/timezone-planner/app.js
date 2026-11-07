CS.apps['timezone-planner'] = function (root) {
  var el = CS.el;
  var zones = (Intl.supportedValuesOf ? Intl.supportedValuesOf('timeZone') : ['UTC', 'Asia/Kolkata', 'Europe/London', 'America/New_York']);
  var mine = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
  var S = { base: mine, date: new Date().toISOString().slice(0, 10), time: '10:00', others: ['Europe/London', 'America/New_York', 'Asia/Dubai', 'Australia/Sydney'].filter(function (z) { return z !== mine; }) };
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' });
  var dl = el('datalist', { id: 'tz-list' }); zones.forEach(function (z) { dl.appendChild(el('option', { value: z })); }); form.appendChild(dl);
  var base = CS.inp('text', S.base, function (v) { if (zones.indexOf(v) > -1 || v === 'UTC') { S.base = v; draw(); } }); base.setAttribute('list', 'tz-list');
  CS.add(form, 'Your time zone', base);
  CS.add(form, 'Date', CS.inp('date', S.date, function (v) { S.date = v; draw(); }));
  CS.add(form, 'Time', CS.inp('time', S.time, function (v) { S.time = v; draw(); }));
  var other = CS.area(S.others.join('\n'), function (v) { S.others = v.split('\n').map(function (s) { return s.trim(); }).filter(function (z) { return zones.indexOf(z) > -1 || z === 'UTC'; }); draw(); }, 5);
  CS.add(form, 'Other time zones, one per line', other, 'Use names like Europe/Berlin or America/Chicago.');
  function offset(zone, d) {
    var p = new Intl.DateTimeFormat('en-US', { timeZone: zone, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }).formatToParts(d);
    var g = {}; p.forEach(function (x) { g[x.type] = x.value; });
    return (Date.UTC(+g.year, g.month - 1, +g.day, +g.hour % 24, +g.minute, +g.second) - d.getTime()) / 60000;
  }
  function instant() {
    var b = S.date.split('-'), t = S.time.split(':');
    var guess = Date.UTC(+b[0], b[1] - 1, +b[2], +t[0], +t[1]);
    var o1 = offset(S.base, new Date(guess)), t1 = guess - o1 * 60000, o2 = offset(S.base, new Date(t1));
    return new Date(guess - o2 * 60000);
  }
  function draw() {
    out.textContent = '';
    if (!S.date || !S.time) return;
    var when = instant();
    var rows = [S.base].concat(S.others).map(function (z) {
      var f = new Intl.DateTimeFormat(undefined, { timeZone: z, weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }).format(when);
      var h = +new Intl.DateTimeFormat('en-US', { timeZone: z, hour: 'numeric', hourCycle: 'h23' }).format(when);
      return { 'Time zone': z, 'Local time': f, 'Fits the working day': h >= 9 && h < 18 ? 'Yes' : (h >= 7 && h < 21 ? 'Early or late' : 'No') };
    });
    out.appendChild(CS.value(rows));
    out.appendChild(el('p', { class: 'hint', style: 'margin-top:12px', text: 'Working day means 9:00 to 18:00 local time. Daylight saving is handled automatically by your browser.' }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
