CS.apps['text-toolkit'] = function (root) {
  var el = CS.el, inp = CS.area('', draw, 10, 'Paste or type your text'), stats = el('div'), out = CS.out('');
  function draw() {
    var t = inp.value, words = (t.trim().match(/\S+/g) || []).length;
    var sentences = (t.match(/[^.!?]+[.!?]+(\s|$)/g) || []).length || (words ? 1 : 0);
    var paras = t.split(/\n\s*\n/).filter(function (p) { return p.trim(); }).length;
    stats.textContent = '';
    stats.appendChild(CS.value({ 'Words': words, 'Characters': t.length, 'Characters without spaces': t.replace(/\s/g, '').length, 'Sentences': sentences, 'Paragraphs': paras, 'Reading time': words ? Math.max(1, Math.round(words / 200)) + ' min' : '—', 'Speaking time': words ? Math.max(1, Math.round(words / 130)) + ' min' : '—' }));
  }
  var small = /^(a|an|and|as|at|but|by|for|in|nor|of|on|or|the|to|up|via)$/i;
  var ops = {
    'UPPERCASE': function (s) { return s.toUpperCase(); },
    'lowercase': function (s) { return s.toLowerCase(); },
    'Title Case': function (s) { return s.toLowerCase().replace(/\b[\w'’]+/g, function (w, i) { return i && small.test(w) ? w : w.charAt(0).toUpperCase() + w.slice(1); }); },
    'Sentence case': function (s) { return s.toLowerCase().replace(/(^\s*|[.!?]\s+)([a-z])/g, function (m, a, b) { return a + b.toUpperCase(); }); },
    'Make a slug': function (s) { return s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); },
    'Tidy spaces': function (s) { return s.replace(/[ \t]+/g, ' ').replace(/ *\n */g, '\n').replace(/\n{3,}/g, '\n\n').trim(); },
    'Remove duplicate lines': function (s) { var seen = {}; return s.split('\n').filter(function (l) { var k = l.trim(); if (seen[k]) return false; seen[k] = 1; return true; }).join('\n'); },
    'Sort lines A to Z': function (s) { return s.split('\n').filter(function (l) { return l.trim(); }).sort(function (a, b) { return a.localeCompare(b); }).join('\n'); }
  };
  var bar = el('div', { class: 'seg', style: 'margin-top:12px' });
  Object.keys(ops).forEach(function (k) { bar.appendChild(el('button', { class: 'btn ghost', type: 'button', text: k, onclick: function () { out.set(ops[k](inp.value)); } })); });
  var left = el('div', { class: 'panel' }); CS.add(left, 'Text', inp); left.appendChild(bar);
  var right = el('div', { class: 'stack' }, [el('div', { class: 'panel' }, [stats]), el('div', { class: 'panel' }, [el('label', { text: 'Result of the last change' }), out])]);
  draw();
  root.appendChild(el('div', { class: 'cols' }, [left, right]));
};
