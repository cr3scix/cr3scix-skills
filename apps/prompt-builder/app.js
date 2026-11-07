CS.apps['prompt-builder'] = function (root) {
  var el = CS.el;
  var F = { role: '', task: '', context: '', input: '', steps: '', format: 'Short paragraphs', tone: 'Clear and friendly', rules: '', example: '' };
  var presets = {
    'Start blank': { role: '', task: '', context: '', input: '', steps: '', rules: '', example: '' },
    'Reply to a customer email': { role: 'a helpful customer support lead at a small business', task: 'Write a reply to the customer email below.', context: 'We want the customer to feel heard and know what happens next.', input: 'The customer email, plus any order or account details.', steps: 'Acknowledge the problem\nSay what we will do and by when\nOffer a way to reach us', rules: 'Do not promise refunds unless told to\nKeep it under 150 words', example: '' },
    'Summarise a meeting': { role: 'an experienced chief of staff', task: 'Turn the meeting notes below into a summary.', context: 'The summary goes to people who were not in the meeting.', input: 'Raw meeting notes or a transcript.', steps: 'List the decisions made\nList action items with an owner and a due date\nNote open questions', rules: 'Only use what is in the notes\nMark anything unclear as unclear', example: '' },
    'Review code': { role: 'a careful senior engineer doing a code review', task: 'Review the code below and point out problems.', context: 'It will run in production and be maintained by a small team.', input: 'The code and a one-line description of what it should do.', steps: 'Check correctness and edge cases\nCheck security\nSuggest simpler options where they exist', rules: 'Explain why each issue matters\nDo not rewrite everything, point to the lines', example: '' },
    'Write a product description': { role: 'an e-commerce copywriter', task: 'Write a product description from the details below.', context: 'The page is for shoppers comparing similar products.', input: 'Product name, key features, materials, size, price.', steps: 'Lead with the main benefit\nCover the key features in plain words\nEnd with who it is best for', rules: 'No hype words\nDo not invent features', example: '' }
  };
  var form = el('div', { class: 'panel' }), right = el('div', { class: 'panel' }), out = CS.out('');
  var fields = [];
  CS.add(form, 'Start from a preset', CS.sel(Object.keys(presets), 'Start blank', function (v) { Object.keys(presets[v]).forEach(function (k) { F[k] = presets[v][k]; }); fields.forEach(function (f) { f[1].value = F[f[0]]; }); draw(); }));
  function add(key, label, ctl, hint) { fields.push([key, ctl]); CS.add(form, label, ctl, hint); }
  add('role', 'Role', CS.inp('text', '', function (v) { F.role = v; draw(); }, 'for example an experienced HR manager'), 'Who the assistant should act as.');
  add('task', 'Task', CS.area('', function (v) { F.task = v; draw(); }, 3), 'What you want done, in one or two sentences.');
  add('context', 'Context', CS.area('', function (v) { F.context = v; draw(); }, 3), 'Who it is for and why. Background the assistant cannot guess.');
  add('input', 'What you will paste in', CS.inp('text', '', function (v) { F.input = v; draw(); }));
  add('steps', 'Steps to follow (one per line)', CS.area('', function (v) { F.steps = v; draw(); }, 3));
  add('rules', 'Rules (one per line)', CS.area('', function (v) { F.rules = v; draw(); }, 3), 'What to avoid, length limits, things not to invent.');
  add('example', 'Example of a good answer (optional)', CS.area('', function (v) { F.example = v; draw(); }, 3));
  CS.add(form, 'Output format', CS.sel(['Short paragraphs', 'Bullet list', 'Table', 'Markdown report with headings', 'JSON', 'Numbered steps'], F.format, function (v) { F.format = v; draw(); }));
  CS.add(form, 'Tone', CS.sel(['Clear and friendly', 'Formal', 'Direct and brief', 'Warm and personal', 'Technical and precise'], F.tone, function (v) { F.tone = v; draw(); }));
  function lines(s) { return s.split('\n').map(function (l) { return l.trim(); }).filter(Boolean); }
  function draw() {
    var p = [];
    p.push('You are ' + (F.role.trim() ? F.role.trim().replace(/^(a|an)\s+/i, function (m) { return m; }) : 'a helpful assistant') + '.');
    if (F.task.trim()) p.push('<task>\n' + F.task.trim() + '\n</task>');
    if (F.context.trim()) p.push('<context>\n' + F.context.trim() + '\n</context>');
    if (F.input.trim()) p.push('<input>\nI will give you: ' + F.input.trim() + '\n</input>');
    if (lines(F.steps).length) p.push('<steps>\n' + lines(F.steps).map(function (l, i) { return (i + 1) + '. ' + l; }).join('\n') + '\n</steps>');
    if (lines(F.rules).length) p.push('<rules>\n' + lines(F.rules).map(function (l) { return '- ' + l; }).join('\n') + '\n</rules>');
    if (F.example.trim()) p.push('<example>\n' + F.example.trim() + '\n</example>');
    p.push('<output>\nFormat: ' + F.format + '.\nTone: ' + F.tone + '.\nIf something you need is missing, ask me before you answer.\n</output>');
    out.set(p.join('\n\n'));
  }
  draw();
  CS.add(right, 'Your prompt', out, 'Paste this into any AI assistant, then add your material under it.');
  root.appendChild(el('div', { class: 'cols' }, [form, right]));
};
