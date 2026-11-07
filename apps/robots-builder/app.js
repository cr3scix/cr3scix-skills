CS.apps['robots-builder'] = function (root) {
  var el = CS.el;
  var bots = [
    ['OAI-SearchBot', 'OpenAI', 'search'], ['ChatGPT-User', 'OpenAI', 'user'], ['GPTBot', 'OpenAI', 'training'],
    ['Claude-SearchBot', 'Anthropic', 'search'], ['Claude-User', 'Anthropic', 'user'], ['ClaudeBot', 'Anthropic', 'training'],
    ['PerplexityBot', 'Perplexity', 'search'], ['Perplexity-User', 'Perplexity', 'user'],
    ['Google-Extended', 'Google', 'training'], ['Applebot-Extended', 'Apple', 'training'], ['Meta-ExternalAgent', 'Meta', 'training'],
    ['CCBot', 'Common Crawl', 'training'], ['Bytespider', 'ByteDance', 'training'], ['Amazonbot', 'Amazon', 'training']
  ];
  var kinds = { search: 'Search and citations', user: 'Fetches a page when a person asks', training: 'Collects content for model training' };
  var S = { allow: {}, sitemap: 'https://example.com/sitemap.xml', paths: '/admin/\n/cart/', sSearch: 'yes', sInput: 'yes', sTrain: 'no' };
  var form = el('div', { class: 'panel' }), out = CS.out('');
  function preset(kind) { bots.forEach(function (b) { S.allow[b[0]] = kind === 'all' ? true : kind === 'none' ? false : b[2] !== 'training'; }); }
  preset('notrain');
  var boxes = {};
  form.appendChild(el('div', { class: 'seg' }, [
    el('button', { class: 'btn ghost', type: 'button', text: 'Allow search, block training', onclick: function () { preset('notrain'); sync(); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Allow all', onclick: function () { preset('all'); sync(); } }),
    el('button', { class: 'btn ghost', type: 'button', text: 'Block all', onclick: function () { preset('none'); sync(); } })
  ]));
  Object.keys(kinds).forEach(function (k) {
    form.appendChild(el('h2', { text: kinds[k], style: 'margin:18px 0 6px;font-size:.8rem' }));
    bots.filter(function (b) { return b[2] === k; }).forEach(function (b) {
      var c = el('input', { type: 'checkbox', id: 'bot-' + b[0] }); boxes[b[0]] = c;
      c.addEventListener('change', function () { S.allow[b[0]] = c.checked; draw(); });
      form.appendChild(el('label', { for: 'bot-' + b[0], style: 'display:flex;gap:8px;align-items:center;font-weight:400;margin:6px 0' }, [c, el('span', { text: b[0] }), el('span', { class: 'hint', text: b[1] })]));
    });
  });
  form.appendChild(el('h2', { text: 'Content signals', style: 'margin:18px 0 6px;font-size:.8rem' }));
  var yn = [['yes', 'Yes'], ['no', 'No']];
  CS.add(form, 'Allow use for search', CS.sel(yn, S.sSearch, function (v) { S.sSearch = v; draw(); }));
  CS.add(form, 'Allow use as live AI answer input', CS.sel(yn, S.sInput, function (v) { S.sInput = v; draw(); }));
  CS.add(form, 'Allow use for training AI models', CS.sel(yn, S.sTrain, function (v) { S.sTrain = v; draw(); }));
  CS.add(form, 'Sitemap URL', CS.inp('text', S.sitemap, function (v) { S.sitemap = v; draw(); }));
  CS.add(form, 'Paths to keep everyone out of (one per line)', CS.area(S.paths, function (v) { S.paths = v; draw(); }, 3));
  function sync() { bots.forEach(function (b) { boxes[b[0]].checked = !!S.allow[b[0]]; }); draw(); }
  function draw() {
    var paths = S.paths.split('\n').map(function (l) { return l.trim(); }).filter(Boolean);
    var t = '# Generated with Cr3scix Skills\n';
    bots.forEach(function (b) { if (!S.allow[b[0]]) t += '\nUser-agent: ' + b[0] + '\nDisallow: /\n'; });
    t += '\nUser-agent: *\nContent-Signal: search=' + S.sSearch + ', ai-input=' + S.sInput + ', ai-train=' + S.sTrain + '\nAllow: /\n';
    paths.forEach(function (p) { t += 'Disallow: ' + (p.charAt(0) === '/' ? p : '/' + p) + '\n'; });
    if (S.sitemap.trim()) t += '\nSitemap: ' + S.sitemap.trim() + '\n';
    out.set(t);
  }
  var right = el('div', { class: 'panel' });
  CS.add(right, 'Your robots.txt', out, 'Save it as robots.txt at the top of your site. Crawlers choose whether to obey it, so it is a request, not a lock. Crawler names change over time, so check each company\'s documentation now and then.');
  sync();
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
