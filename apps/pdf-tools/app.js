CS.apps['pdf-tools'] = function (root) {
  var el = CS.el, files = [];
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' }), list = el('div'), msg = el('div', { 'aria-live': 'polite', style: 'margin-top:10px' });
  var mode = 'Merge', ranges = CS.inp('text', '', null, 'for example 1-3, 5, 8-');
  var pick = el('input', { type: 'file', accept: 'application/pdf', multiple: 'multiple' });
  pick.addEventListener('change', function () { files = files.concat(Array.prototype.slice.call(pick.files || [])); pick.value = ''; drawList(); });
  CS.add(form, 'What do you want to do?', CS.sel(['Merge', 'Extract pages'], mode, function (v) { mode = v; rangeBox.style.display = v === 'Merge' ? 'none' : ''; }));
  CS.add(form, 'PDF files', pick, 'Processed in your browser. Nothing is uploaded.');
  form.appendChild(list);
  var rangeBox = el('div', { style: 'display:none' }); CS.add(rangeBox, 'Pages to keep (from the first file)', ranges, 'Commas between parts. "8-" means page 8 to the end.'); form.appendChild(rangeBox);
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'Make PDF', onclick: run })]));
  form.appendChild(msg);
  out.appendChild(el('div', { class: 'empty', text: 'Your new PDF will be ready to download here.' }));
  function drawList() {
    list.textContent = '';
    files.forEach(function (f, i) {
      list.appendChild(el('div', { class: 'hist' }, [el('span', { text: (i + 1) + '. ' + f.name }), el('span', { class: 'seg' }, [
        el('button', { class: 'btn ghost', type: 'button', text: 'Up', 'aria-label': 'Move ' + f.name + ' up', onclick: function () { if (i) { files.splice(i - 1, 0, files.splice(i, 1)[0]); drawList(); } } }),
        el('button', { class: 'btn ghost', type: 'button', text: 'Remove', 'aria-label': 'Remove ' + f.name, onclick: function () { files.splice(i, 1); drawList(); } })
      ])]));
    });
  }
  function say(t, bad) { msg.textContent = ''; msg.appendChild(el('div', { class: bad ? 'err' : 'hint', role: bad ? 'alert' : 'status', text: t })); }
  function parse(spec, total) {
    var pages = [];
    spec.split(',').forEach(function (part) {
      var m = part.trim().match(/^(\d+)?\s*(-)?\s*(\d+)?$/); if (!m || !part.trim()) throw new Error('Could not read "' + part.trim() + '".');
      var a = m[1] ? +m[1] : 1, b = m[2] ? (m[3] ? +m[3] : total) : a;
      if (a < 1 || b > total || a > b) throw new Error('Pages must be between 1 and ' + total + '.');
      for (var p = a; p <= b; p++) pages.push(p - 1);
    });
    return pages;
  }
  function run() {
    if (!files.length) { say('Add at least one PDF.', true); return; }
    if (mode === 'Merge' && files.length < 2) { say('Add two or more PDFs to merge.', true); return; }
    say('Working...');
    CS.load('pdf-tools', 'pdf-lib.min.js').then(function () {
      var L = window.PDFLib;
      return L.PDFDocument.create().then(function (outDoc) {
        var chosen = mode === 'Merge' ? files : files.slice(0, 1);
        return chosen.reduce(function (p, f) {
          return p.then(function () { return f.arrayBuffer(); }).then(function (buf) { return L.PDFDocument.load(buf, { ignoreEncryption: false }); }).then(function (src) {
            var idx = mode === 'Merge' ? src.getPageIndices() : parse(ranges.value || '1-', src.getPageCount());
            return outDoc.copyPages(src, idx).then(function (pages) { pages.forEach(function (pg) { outDoc.addPage(pg); }); });
          });
        }, Promise.resolve()).then(function () { return outDoc.save(); }).then(function (bytes) {
          var blob = new Blob([bytes], { type: 'application/pdf' }), name = mode === 'Merge' ? 'merged.pdf' : 'extracted-pages.pdf';
          out.textContent = '';
          var pc = outDoc.getPageCount(); out.appendChild(el('p', { text: pc + (pc === 1 ? ' page, ' : ' pages, ') + Math.max(1, Math.round(blob.size / 1024)) + ' KB.' }));
          out.appendChild(el('button', { class: 'btn', type: 'button', text: 'Download ' + name, onclick: function () { CS.download(name, blob); } }));
          say('Done.');
        });
      });
    }).catch(function (e) { say(/encrypt/i.test(e.message) ? 'One of the files is password protected. Remove the password first.' : (e.message || 'Something went wrong with that file.'), true); });
  }
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
