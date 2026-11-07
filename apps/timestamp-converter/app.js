CS.apps['timestamp-converter'] = function (root) {
  var el = CS.el, inp = CS.inp('text', String(Math.floor(Date.now() / 1000)), draw, 'Unix seconds, milliseconds or a date'), out = el('div', { class: 'panel' });
  var form = el('div', { class: 'panel' });
  CS.add(form, 'Timestamp or date', inp, 'Try 1735689600, 1735689600000, 2026-03-01 14:30 or 2026-03-01T14:30:00Z.');
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn ghost', type: 'button', text: 'Now', onclick: function () { inp.value = String(Math.floor(Date.now() / 1000)); draw(); } })]));
  function rel(ms) { var s = Math.round((ms - Date.now()) / 1000), a = Math.abs(s), u = [[31536000, 'year'], [2592000, 'month'], [86400, 'day'], [3600, 'hour'], [60, 'minute'], [1, 'second']].find(function (x) { return a >= x[0]; }) || [1, 'second']; return new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' }).format(Math.round(s / u[0]), u[1]); }
  function draw() {
    out.textContent = '';
    var v = inp.value.trim(), d;
    if (/^-?\d+(\.\d+)?$/.test(v)) { var n = Number(v); d = new Date(Math.abs(n) >= 1e11 ? n : n * 1000); }
    else if (v) { d = new Date(/^\d{4}-\d\d-\d\d \d/.test(v) ? v.replace(' ', 'T') : v); }
    if (!d || isNaN(d)) { out.appendChild(el('div', { class: 'empty', text: 'Type a timestamp or a date.' })); return; }
    out.appendChild(CS.value({ 'Unix seconds': Math.floor(d.getTime() / 1000), 'Unix milliseconds': d.getTime(), 'ISO 8601 (UTC)': d.toISOString(), 'Your time': d.toLocaleString(undefined, { dateStyle: 'full', timeStyle: 'long' }), 'Relative': rel(d.getTime()), 'India (IST)': d.toLocaleString('en-IN', { timeZone: 'Asia/Kolkata', dateStyle: 'medium', timeStyle: 'short' }), 'London': d.toLocaleString('en-GB', { timeZone: 'Europe/London', dateStyle: 'medium', timeStyle: 'short' }), 'New York': d.toLocaleString('en-US', { timeZone: 'America/New_York', dateStyle: 'medium', timeStyle: 'short' }) }));
  }
  draw();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
