CS.apps['json-tools'] = function (root) {
  var el = CS.el, inp = CS.area('', null, 14, 'Paste JSON here'), status = el('div', { 'aria-live': 'polite' }), out = CS.out('');
  var indent = CS.sel([['2', '2 spaces'], ['4', '4 spaces'], ['\t', 'Tabs']], '2');
  function parse() {
    try { return { ok: true, v: JSON.parse(inp.value) }; }
    catch (e) {
      var m = /position (\d+)/.exec(e.message), where = '';
      if (m) { var pre = inp.value.slice(0, Number(m[1])).split('\n'); where = ' (line ' + pre.length + ', column ' + (pre[pre.length - 1].length + 1) + ')'; }
      return { ok: false, msg: e.message.replace(/ in JSON at position \d+.*/, '') + where };
    }
  }
  function say(ok, msg) { status.textContent = ''; status.appendChild(el('div', { class: ok ? 'hint' : 'err', role: ok ? 'status' : 'alert', text: msg })); }
  function sortKeys(v) { if (Array.isArray(v)) return v.map(sortKeys); if (v && typeof v === 'object') { var o = {}; Object.keys(v).sort().forEach(function (k) { o[k] = sortKeys(v[k]); }); return o; } return v; }
  function act(kind) {
    if (!inp.value.trim()) { say(false, 'Paste some JSON first.'); return; }
    var r = parse();
    if (!r.ok) { say(false, 'Invalid JSON: ' + r.msg); return; }
    var ind = indent.value === '\t' ? '\t' : Number(indent.value);
    var v = kind === 'sort' ? sortKeys(r.v) : r.v;
    out.set(kind === 'min' ? JSON.stringify(v) : JSON.stringify(v, null, ind));
    say(true, kind === 'validate' ? 'Valid JSON.' : 'Done. ' + out.querySelector('pre').textContent.length.toLocaleString() + ' characters.');
  }
  var bar = el('div', { class: 'row' }, [
    el('button', { class: 'btn', type: 'button', text: 'Format', onclick: function () { act('format'); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Minify', onclick: function () { act('min'); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Sort keys', onclick: function () { act('sort'); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Validate only', onclick: function () { act('validate'); } }),
    indent
  ]);
  indent.style.width = 'auto';
  var left = el('div', { class: 'panel' }); CS.add(left, 'Input', inp); left.appendChild(bar); left.appendChild(status);
  var right = el('div', { class: 'panel' }); CS.add(right, 'Result', out);
  root.appendChild(el('div', { class: 'cols', style: 'grid-template-columns:1fr 1fr' }, [left, right]));
};
