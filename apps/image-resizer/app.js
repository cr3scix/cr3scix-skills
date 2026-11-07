CS.apps['image-resizer'] = function (root) {
  var el = CS.el;
  var presets = [['custom', 'Custom size'], ['1080x1350', 'Instagram portrait 1080 x 1350'], ['1080x1080', 'Square post 1080 x 1080'], ['1080x1920', 'Story, Reel or Short 1080 x 1920'], ['1200x627', 'LinkedIn link image 1200 x 627'], ['1200x630', 'Website share image 1200 x 630'], ['1280x720', 'YouTube thumbnail 1280 x 720'], ['1500x500', 'X header 1500 x 500']];
  var S = { w: 1080, h: 1350, fit: 'cover', fmt: 'image/jpeg', q: 82, maxW: 0 };
  var files = [], form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' });
  var file = el('input', { type: 'file', accept: 'image/*', multiple: 'multiple' });
  file.addEventListener('change', function () { files = Array.prototype.slice.call(file.files || []); render(); });
  CS.add(form, 'Images', file, 'Processed in your browser. Nothing is uploaded.');
  var wIn = CS.inp('number', S.w, function (v) { S.w = Math.max(1, Math.min(8000, Number(v) || 1)); }), hIn = CS.inp('number', S.h, function (v) { S.h = Math.max(1, Math.min(8000, Number(v) || 1)); });
  CS.add(form, 'Size preset', CS.sel(presets, '1080x1350', function (v) { if (v !== 'custom') { var p = v.split('x'); S.w = +p[0]; S.h = +p[1]; wIn.value = S.w; hIn.value = S.h; } }));
  var g = el('div', { class: 'grid2' }, [el('div', {}, [el('label', { text: 'Width' }), wIn]), el('div', {}, [el('label', { text: 'Height' }), hIn])]);
  form.appendChild(g);
  CS.add(form, 'Fit', CS.sel([['cover', 'Fill and crop the edges'], ['contain', 'Fit inside with a border'], ['scale', 'Keep shape, use width only']], S.fit, function (v) { S.fit = v; }));
  CS.add(form, 'Format', CS.sel([['image/jpeg', 'JPEG (photos)'], ['image/webp', 'WebP (smallest)'], ['image/png', 'PNG (sharp graphics)']], S.fmt, function (v) { S.fmt = v; }));
  CS.add(form, 'Quality (JPEG and WebP)', CS.inp('number', S.q, function (v) { S.q = Math.max(10, Math.min(100, Number(v) || 82)); }), '75 to 85 keeps photos sharp at a fraction of the size.');
  form.appendChild(el('div', { class: 'row' }, [el('button', { class: 'btn', type: 'button', text: 'Resize', onclick: render })]));
  function load(f) { return new Promise(function (ok, bad) { var img = new Image(); img.onload = function () { ok(img); }; img.onerror = bad; img.src = URL.createObjectURL(f); }); }
  function kb(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB'; }
  function render() {
    out.textContent = '';
    if (!files.length) { out.appendChild(el('div', { class: 'empty', text: 'Choose one or more images.' })); return; }
    files.forEach(function (f) {
      var row = el('div', { class: 'hist' }, [el('span', { text: f.name + ' · working...' })]);
      out.appendChild(row);
      load(f).then(function (img) {
        var tw = S.w, th = S.fit === 'scale' ? Math.round(img.height * S.w / img.width) : S.h;
        var c = el('canvas'); c.width = tw; c.height = th;
        var x = c.getContext('2d');
        x.imageSmoothingQuality = 'high';
        if (S.fmt !== 'image/png' || S.fit === 'contain') { x.fillStyle = '#ffffff'; x.fillRect(0, 0, tw, th); }
        var r = S.fit === 'contain' ? Math.min(tw / img.width, th / img.height) : Math.max(tw / img.width, th / img.height);
        if (S.fit === 'scale') r = tw / img.width;
        var dw = img.width * r, dh = img.height * r;
        x.drawImage(img, (tw - dw) / 2, (th - dh) / 2, dw, dh);
        URL.revokeObjectURL(img.src);
        c.toBlob(function (b) {
          var ext = S.fmt.split('/')[1].replace('jpeg', 'jpg'), name = f.name.replace(/\.[^.]+$/, '') + '-' + tw + 'x' + th + '.' + ext;
          row.textContent = '';
          var thumb = el('img', { src: URL.createObjectURL(b), alt: 'Resized ' + f.name, style: 'width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--line)' });
          row.appendChild(el('div', { style: 'display:flex;gap:10px;align-items:center' }, [thumb, el('div', {}, [el('b', { text: name }), el('br'), el('small', { text: kb(f.size) + ' → ' + kb(b.size) })])]));
          row.appendChild(el('button', { class: 'btn ghost', type: 'button', text: 'Download', onclick: function () { CS.download(name, b); } }));
        }, S.fmt, S.q / 100);
      }, function () { row.textContent = f.name + ': could not read this image.'; });
    });
  }
  render();
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};
