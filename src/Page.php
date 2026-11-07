<?php
declare(strict_types=1);

final class Page
{
    private static function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function llms(Registry $registry, string $base, ?Apps $apps = null): string
    {
        $out = "# Cr3scix Skills\n\n> Open-source collection of skills for businesses and developers. Every skill is available in a web catalog, as a REST endpoint and as an MCP tool.\n\n";
        foreach (Registry::CATEGORIES as $key => $label) {
            $items = array_filter($registry->all(), fn($d) => $d['category'] === $key);
            if (!$items) {
                continue;
            }
            $out .= "## $label\n\n";
            foreach ($items as $name => $def) {
                $out .= "- [{$def['title']}]($base/api/skills/$name): {$def['description']}\n";
            }
            $out .= "\n";
        }
        if ($apps && $apps->all()) {
            $out .= "## Interactive apps (browser tools)\n\n";
            foreach ($apps->all() as $name => $def) {
                $out .= "- [{$def['title']}]($base/#/app/$name): {$def['description']}\n";
            }
            $out .= "\n";
        }
        $out .= "## Connect\n\n- [MCP endpoint]($base/mcp): Streamable HTTP, POST JSON-RPC 2.0\n";
        $out .= "- [MCP server card]($base/.well-known/mcp/server-card.json): describes the MCP server\n";
        $out .= "- [Skill list]($base/api/skills): JSON list of all skills\n";
        return $out;
    }

    public static function catalog(Registry $registry, string $base, ?Apps $apps = null): string
    {
        $skills = [];
        foreach ($registry->all() as $name => $def) {
            $skills[] = [
                'name' => $name,
                'title' => $def['title'],
                'description' => $def['description'],
                'category' => $def['category'],
                'network' => !empty($def['network']),
                'inputSchema' => $def['inputSchema'],
            ];
        }
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
        $appList = [];
        foreach ($apps ? $apps->all() : [] as $name => $def) {
            $appList[] = ['name' => $name, 'title' => $def['title'], 'description' => $def['description'], 'category' => $def['category']];
        }
        $data = json_encode(['skills' => $skills, 'apps' => $appList, 'categories' => Registry::CATEGORIES, 'base' => $base, 'version' => SKILLS_VERSION], $flags);
        return str_replace(['{{DATA}}', '{{BASE}}'], [$data, self::e($base)], self::TEMPLATE);
    }

    private const TEMPLATE = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cr3scix Skills</title>
<meta name="description" content="A dashboard of open-source skills for businesses and developers. Run them here, call them over an API, or connect them to an AI tool through MCP.">
<link rel="alternate" type="text/plain" href="{{BASE}}/llms.txt">
<style>
:root{--bg:#f4f2ed;--panel:#fff;--side:#ebe8e0;--fg:#1a1917;--muted:#5d5a54;--line:#dcd8cf;--accent:#1a1917;--accent-fg:#f6f4ef;--ok:#14633a;--warn:#8a5a00;--bad:#9b1c1c;--okbg:#e3f2e8;--warnbg:#f7ecd2;--badbg:#f7dede;--code:#f0ede6}
@media (prefers-color-scheme:dark){:root{--bg:#0d0d0d;--panel:#161615;--side:#121211;--fg:#eeedeb;--muted:#a09d96;--line:#2b2a28;--accent:#eeedeb;--accent-fg:#0d0d0d;--ok:#6ad79a;--warn:#e8c26a;--bad:#f08a8a;--okbg:#10281a;--warnbg:#2c2410;--badbg:#2e1515;--code:#1d1c1a}}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:var(--bg);color:var(--fg);font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
a{color:inherit}
button{font:inherit;cursor:pointer}
.app{display:grid;grid-template-columns:272px 1fr;min-height:100%}
aside{background:var(--side);border-right:1px solid var(--line);padding:18px 14px;position:sticky;top:0;height:100vh;overflow:auto}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1.05rem;padding:4px 8px 14px;text-decoration:none}
.logo{width:28px;height:28px;border-radius:8px;background:var(--accent);color:var(--accent-fg);display:grid;place-items:center;font-size:.85rem}
.search{width:100%;padding:9px 11px;border:1px solid var(--line);border-radius:9px;background:var(--panel);color:var(--fg);font:inherit;margin-bottom:12px}
.nav a,.nav button.item{display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;padding:8px 10px;border-radius:8px;text-decoration:none;color:var(--fg);background:none;border:0;text-align:left}
.nav a:hover,.nav button.item:hover{background:var(--line)}
.nav a.on{background:var(--accent);color:var(--accent-fg)}
.nav h4{margin:16px 10px 6px;font-size:.7rem;text-transform:uppercase;letter-spacing:.09em;color:var(--muted)}
.star{color:var(--warn)}
main{padding:28px clamp(16px,4vw,44px) 80px;max-width:1080px;width:100%}
.topbar{display:none}
h1{font-size:clamp(1.6rem,3.6vw,2.2rem);margin:0 0 6px;letter-spacing:-.02em}
h2{font-size:1rem;margin:30px 0 12px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
.lead{color:var(--muted);margin:0 0 20px;max-width:62ch}
.stats{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
.stat,.tile,.panel{background:var(--panel);border:1px solid var(--line);border-radius:14px}
.stat{padding:14px 16px}.stat b{display:block;font-size:1.6rem;line-height:1.2}.stat span{color:var(--muted);font-size:.85rem}
.tiles{display:grid;gap:12px;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr))}
.tile{padding:16px;text-align:left;color:var(--fg);display:flex;flex-direction:column;gap:6px;text-decoration:none}
.tile:hover{border-color:var(--fg)}
.tile h3{margin:0;font-size:1rem;display:flex;justify-content:space-between;gap:8px}
.tile p{margin:0;color:var(--muted);font-size:.88rem}
.badge{display:inline-block;font-size:.7rem;padding:2px 8px;border-radius:999px;border:1px solid var(--line);color:var(--muted);width:fit-content}
.panel{padding:20px}
.skillhead{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap}
.cols{display:grid;gap:18px;grid-template-columns:minmax(0,380px) minmax(0,1fr);align-items:start;margin-top:18px}
label{display:block;font-size:.84rem;margin:12px 0 4px;font-weight:600}
label:first-child{margin-top:0}
input,select,textarea{width:100%;padding:9px 10px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--fg);font:inherit}
input[type=checkbox]{width:auto}
textarea{min-height:90px;resize:vertical}
.hint{font-size:.78rem;color:var(--muted);margin-top:2px}
.btn{padding:9px 16px;border:0;border-radius:999px;background:var(--accent);color:var(--accent-fg);font-weight:600}
.btn.ghost{background:none;color:var(--fg);border:1px solid var(--line);font-weight:500;padding:6px 12px;font-size:.85rem}
.btn[disabled]{opacity:.5;cursor:wait}
.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:16px}
.empty{color:var(--muted);padding:28px;text-align:center;border:1px dashed var(--line);border-radius:12px}
.err{background:var(--badbg);color:var(--bad);padding:12px 14px;border-radius:10px}
.score{display:flex;align-items:center;gap:16px;margin-bottom:14px}
.ring{width:84px;height:84px;border-radius:50%;display:grid;place-items:center;font-weight:700;font-size:1.5rem;flex:none}
.kv{display:grid;grid-template-columns:minmax(110px,170px) 1fr;gap:8px 14px;margin:0}
.kv dt{color:var(--muted);font-size:.85rem}.kv dd{margin:0;min-width:0;word-break:break-word}
.kv dd>.kv{margin-top:2px}
.code{position:relative;background:var(--code);border:1px solid var(--line);border-radius:10px;padding:12px;margin:2px 0}
.code pre{margin:0;white-space:pre-wrap;word-break:break-word;font-size:.8rem;max-height:340px;overflow:auto}
.code .btn{position:absolute;top:8px;right:8px}
.code.has-btn pre{padding-right:64px}
table{border-collapse:collapse;width:100%;font-size:.85rem}
th,td{text-align:left;padding:6px 8px;border-bottom:1px solid var(--line);vertical-align:top}
th{color:var(--muted);font-weight:600}
.tablewrap{overflow:auto}
ul.plain{margin:0;padding-left:18px}
.check{display:grid;grid-template-columns:auto 1fr auto;gap:4px 10px;padding:9px 0;border-bottom:1px solid var(--line)}
.pill{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:999px;height:fit-content;text-transform:uppercase}
.pill.pass{background:var(--okbg);color:var(--ok)}.pill.partial{background:var(--warnbg);color:var(--warn)}.pill.fail{background:var(--badbg);color:var(--bad)}
.check .fix{grid-column:2/4;font-size:.85rem;color:var(--muted)}
.check .d{grid-column:2/4;font-size:.82rem;color:var(--muted)}
.hist{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid var(--line)}
.hist small{color:var(--muted)}
code{font-size:.85em;background:var(--code);padding:1px 5px;border-radius:5px}
@media print{aside,.topbar,.noprint{display:none!important}.app{display:block}main{padding:0;max-width:none}.printable{border:0}body{background:#fff;color:#000}.cols{display:block}}
.grid2{display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))}
.meter{height:6px;border-radius:99px;background:var(--line);overflow:hidden;margin-top:4px}.meter i{display:block;height:100%;background:var(--ok)}.meter.warn i{background:var(--warn)}.meter.bad i{background:var(--bad)}
mark{background:var(--warnbg);color:inherit;border-radius:3px;padding:0 1px}
.serp{background:#fff;color:#202124;border:1px solid var(--line);border-radius:10px;padding:14px;font-family:Arial,sans-serif}.serp .t{color:#1a0dab;font-size:1.15rem;line-height:1.3}.serp .u{color:#006621;font-size:.85rem}.serp .d{color:#545454;font-size:.88rem}
.irow{display:grid;grid-template-columns:1fr 70px 90px auto;gap:6px;margin-bottom:6px}
.stack>*+*{margin-top:12px}
.seg{display:flex;gap:6px;flex-wrap:wrap}
@media (max-width:860px){
 .app{grid-template-columns:1fr}
 aside{position:static;height:auto;border-right:0;border-bottom:1px solid var(--line);display:none}
 aside.open{display:block}
 .topbar{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;border-bottom:1px solid var(--line);background:var(--side)}
 .cols{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="topbar"><a class="brand" href="#/" style="padding:0"><span class="logo">C</span>Cr3scix Skills</a><button class="btn ghost" id="menu" aria-expanded="false">Menu</button></div>
<div class="app">
<aside id="side"></aside>
<main id="main" tabindex="-1"></main>
</div>
<noscript><p style="padding:16px">This dashboard needs JavaScript. The skills are also available at <a href="{{BASE}}/api/skills">/api/skills</a> and <a href="{{BASE}}/llms.txt">llms.txt</a>.</p></noscript>
<script id="data" type="application/json">{{DATA}}</script>
<script>
(function(){
var D = JSON.parse(document.getElementById('data').textContent);
var BY = {}; D.skills.forEach(function(s){ s.kind = 'skill'; BY[s.name] = s; });
var AP = {}; (D.apps || []).forEach(function(a){ a.kind = 'app'; AP[a.name] = a; });
var ALL = D.skills.concat(D.apps || []);
function fkey(x){ return x.kind === 'app' ? 'app:' + x.name : x.name; }
function byKey(k){ return k.indexOf('app:') === 0 ? AP[k.slice(4)] : BY[k]; }
function matches(x, q){ return !q || (x.title + ' ' + x.description + ' ' + x.name).toLowerCase().indexOf(q) > -1; }
var side = document.getElementById('side'), main = document.getElementById('main');
var query = '';

function store(k, v){ try { if (v === undefined) { return JSON.parse(localStorage.getItem('cs.' + k) || 'null'); } localStorage.setItem('cs.' + k, JSON.stringify(v)); } catch (e) { return null; } }
var favs = store('fav') || [];
var hist = store('hist') || [];
var mem = {}; // fallback copy when storage is blocked
function saveFavs(){ store('fav', favs); }
function el(tag, attrs, kids){
  var n = document.createElement(tag);
  for (var k in (attrs || {})) { if (k === 'text') n.textContent = attrs[k]; else if (k === 'class') n.className = attrs[k]; else if (k.slice(0,2) === 'on') n.addEventListener(k.slice(2), attrs[k]); else n.setAttribute(k, attrs[k]); }
  (kids || []).forEach(function(c){ if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
  return n;
}
function human(k){ return k.replace(/([a-z0-9])([A-Z])/g, '$1 $2').replace(/[_-]+/g, ' ').replace(/^./, function(c){ return c.toUpperCase(); }); }
function route(){ var h = location.hash.replace(/^#\/?/, ''); var p = h.split('/'); return {page: p[0] || 'home', arg: p[1] || ''}; }
function when(ts){ var d = new Date(ts); return d.toLocaleDateString(undefined, {month:'short', day:'numeric'}) + ' ' + d.toLocaleTimeString(undefined, {hour:'2-digit', minute:'2-digit'}); }

window.CS = {apps: {}, el: el, human: human, value: function(v){ return value(v); }, copyBtn: function(l, t){ return copyBtn(l, t); }};
CS.inp = function(type, val, on, ph){ var i = el('input', {type: type || 'text', placeholder: ph || ''}); i.value = val === undefined ? '' : val; if (on) i.addEventListener('input', function(){ on(i.value, i); }); return i; };
CS.area = function(val, on, rows, ph){ var t = el('textarea', {rows: String(rows || 5), placeholder: ph || ''}); t.value = val || ''; if (on) t.addEventListener('input', function(){ on(t.value, t); }); return t; };
CS.sel = function(opts, val, on){ var s = el('select'); opts.forEach(function(o){ var v = Array.isArray(o) ? o[0] : o, l = Array.isArray(o) ? o[1] : o; var op = el('option', {value: v, text: l}); if (String(v) === String(val)) op.selected = true; s.appendChild(op); }); if (on) s.addEventListener('change', function(){ on(s.value); }); return s; };
CS.add = function(parent, label, ctl, hint){ parent.appendChild(el('label', {text: label})); parent.appendChild(ctl); if (hint) parent.appendChild(el('div', {class: 'hint', text: hint})); return ctl; };
CS.out = function(text){ var pre = el('pre', {text: text || ''}); var box = el('div', {class: 'code has-btn'}, [CS.copyBtn('Copy', ''), pre]); var btn = box.firstChild; btn.onclick = null; var fresh = btn.cloneNode(true); box.replaceChild(fresh, btn); fresh.addEventListener('click', function(){ var done = function(){ fresh.textContent = 'Copied'; setTimeout(function(){ fresh.textContent = 'Copy'; }, 1400); }; if (navigator.clipboard) navigator.clipboard.writeText(pre.textContent).then(done, function(){ fresh.textContent = 'Press Ctrl+C'; }); }); box.set = function(t){ pre.textContent = t; }; return box; };
CS.money = function(n, cur){ return (cur || '') + (Number(n) || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

/* ---------- sidebar ---------- */
function renderSide(){
  var r = route();
  side.textContent = '';
  side.appendChild(el('a', {class:'brand', href:'#/'}, [el('span', {class:'logo', text:'C'}), 'Cr3scix Skills']));
  var s = el('input', {class:'search', type:'search', placeholder:'Search skills', 'aria-label':'Search skills', value: query});
  s.addEventListener('input', function(){ query = s.value; renderSide(); var c = side.querySelector('.search'); c.focus(); c.setSelectionRange(query.length, query.length); });
  side.appendChild(s);
  var nav = el('nav', {class:'nav'});
  nav.appendChild(link('#/', 'Overview', r.page === 'home'));
  var q = query.trim().toLowerCase();
  if (favs.length && !q) {
    var fl = favs.map(byKey).filter(Boolean);
    if (fl.length) { nav.appendChild(el('h4', {text:'Favorites'})); fl.forEach(function(x){ nav.appendChild(link('#/' + x.kind + '/' + x.name, x.title, r.page === x.kind && r.arg === x.name, '\u2605')); }); }
  }
  Object.keys(D.categories).forEach(function(cat){
    var items = ALL.filter(function(x){ return x.category === cat && matches(x, q); });
    if (!items.length) return;
    nav.appendChild(el('h4', {text:D.categories[cat]}));
    items.forEach(function(x){ nav.appendChild(link('#/' + x.kind + '/' + x.name, x.title, r.page === x.kind && r.arg === x.name)); });
  });
  if (q && !ALL.some(function(x){ return matches(x, q); })) nav.appendChild(el('p', {class:'hint', text:'Nothing matches that search.', style:'padding:8px 10px'}));
  nav.appendChild(el('h4', {text:'Workspace'}));
  nav.appendChild(link('#/history', 'Recent runs', r.page === 'history'));
  nav.appendChild(link('#/connect', 'Connect and API', r.page === 'connect'));
  side.appendChild(nav);
  side.appendChild(el('p', {class:'hint', text:'v' + D.version + ' · open source', style:'padding:14px 10px 0'}));
}
function link(href, text, on, extra){
  var a = el('a', {href: href, class: on ? 'on' : ''}, [el('span', {text: text})]);
  if (extra) a.appendChild(el('span', {class:'star', text: extra}));
  a.addEventListener('click', function(){ side.classList.remove('open'); });
  return a;
}

/* ---------- pages ---------- */
function home(){
  main.textContent = '';
  main.appendChild(el('h1', {text:'Your skills hub'}));
  main.appendChild(el('p', {class:'lead', text:'Interactive tools you can use right here, plus skills that run on the server. Every skill is also available over a REST API and as an MCP tool, so the same work can run inside your AI assistant.'}));
  var net = D.skills.filter(function(s){ return s.network; }).length;
  main.appendChild(el('div', {class:'stats'}, [
    stat((D.apps || []).length, 'interactive apps'), stat(D.skills.length, 'skills with API and MCP'), stat(Object.keys(D.categories).length, 'categories'), stat(hist.length, 'skill runs on this device')
  ]));
  if (hist.length) {
    main.appendChild(el('h2', {text:'Pick up where you left off'}));
    var t = el('div', {class:'tiles'});
    var seen = {};
    hist.forEach(function(h){ if (BY[h.skill] && !seen[h.skill] && Object.keys(seen).length < 3) { seen[h.skill] = 1; t.appendChild(tile(BY[h.skill])); } });
    main.appendChild(t);
  }
  Object.keys(D.categories).forEach(function(cat){
    var items = ALL.filter(function(x){ return x.category === cat; });
    if (!items.length) return;
    main.appendChild(el('h2', {text:D.categories[cat]}));
    var t2 = el('div', {class:'tiles'});
    items.forEach(function(x){ t2.appendChild(tile(x)); });
    main.appendChild(t2);
  });
}
function stat(n, label){ return el('div', {class:'stat'}, [el('b', {text:String(n)}), el('span', {text:label})]); }
function tile(s){
  var star = favs.indexOf(fkey(s)) > -1;
  return el('a', {class:'tile', href:'#/' + s.kind + '/' + s.name}, [
    el('h3', {}, [el('span', {text:s.title}), star ? el('span', {class:'star', text:'\u2605'}) : null]),
    el('p', {text:s.description}),
    el('span', {class:'badge', text: s.kind === 'app' ? 'Interactive app' : (s.network ? 'Skill \u00b7 fetches a website' : 'Skill \u00b7 runs on server')})
  ]);
}
var loaded = {};
function appPage(name){
  var a = AP[name];
  main.textContent = '';
  if (!a) { main.appendChild(el('div', {class:'empty', text:'That app is not installed.'})); return; }
  var key = 'app:' + name, isFav = favs.indexOf(key) > -1;
  var favBtn = el('button', {class:'btn ghost noprint', type:'button', 'aria-pressed': String(isFav), text: isFav ? '\u2605 Favorited' : '\u2606 Favorite'});
  favBtn.addEventListener('click', function(){ var i = favs.indexOf(key); if (i > -1) favs.splice(i, 1); else favs.push(key); saveFavs(); renderSide(); appPage(name); });
  main.appendChild(el('div', {class:'skillhead noprint'}, [
    el('div', {}, [el('span', {class:'badge', text: D.categories[a.category] + ' \u00b7 app'}), el('h1', {text:a.title, style:'margin-top:8px'}), el('p', {class:'lead', text:a.description, style:'margin:0'})]),
    favBtn
  ]));
  var root = el('div', {style:'margin-top:18px'}, [el('div', {class:'empty', text:'Loading...'})]);
  main.appendChild(root);
  function start(){ root.textContent = ''; try { CS.apps[name](root); } catch (e) { root.appendChild(el('div', {class:'err', role:'alert', text:'This app hit a problem and could not start.'})); if (window.console) console.error(e); } }
  if (CS.apps[name]) { start(); return; }
  var sc = el('script', {src: D.base + '/apps/' + name + '.js'});
  sc.onload = start;
  sc.onerror = function(){ root.textContent = ''; root.appendChild(el('div', {class:'err', role:'alert', text:'Could not load this app. Check your connection and try again.'})); };
  document.head.appendChild(sc);
}

function skillPage(name, prefill){
  var s = BY[name];
  main.textContent = '';
  if (!s) { main.appendChild(el('div', {class:'empty', text:'That skill is not installed.'})); return; }
  var isFav = favs.indexOf(name) > -1;
  var favBtn = el('button', {class:'btn ghost', type:'button', 'aria-pressed': String(isFav), text: isFav ? '★ Favorited' : '☆ Favorite'});
  favBtn.addEventListener('click', function(){
    var i = favs.indexOf(name); if (i > -1) favs.splice(i, 1); else favs.push(name);
    saveFavs(); renderSide(); skillPage(name, currentInput(form, s));
  });
  main.appendChild(el('div', {class:'skillhead'}, [
    el('div', {}, [el('span', {class:'badge', text: D.categories[s.category]}), el('h1', {text:s.title, style:'margin-top:8px'}), el('p', {class:'lead', text:s.description, style:'margin:0'})]),
    favBtn
  ]));
  var form = buildForm(s, prefill || {});
  var out = el('div', {class:'panel', id:'out'}, [el('div', {class:'empty', text:'Results will show up here.'})]);
  form.addEventListener('submit', function(ev){ ev.preventDefault(); run(s, form, out); });
  main.appendChild(el('div', {class:'cols'}, [el('div', {class:'panel'}, [form]), out]));
  if (s.network) main.appendChild(el('p', {class:'hint', text:'This skill fetches pages from the public web on the server. Private addresses are blocked.', style:'margin-top:14px'}));
}
function buildForm(s, pre){
  var f = el('form', {novalidate:'novalidate'});
  var req = s.inputSchema.required || [];
  var props = s.inputSchema.properties || {};
  Object.keys(props).forEach(function(k){
    var r = props[k], id = 'f-' + k, t = r.type || 'string', ctl;
    var val = pre[k] !== undefined ? pre[k] : (r.default !== undefined ? r.default : '');
    if (r.enum) { ctl = el('select', {id:id, name:k}); r.enum.forEach(function(o){ var op = el('option', {text:String(o)}); if (String(o) === String(val)) op.selected = true; ctl.appendChild(op); }); }
    else if (t === 'boolean') { ctl = el('input', {id:id, name:k, type:'checkbox'}); ctl.checked = !!val; }
    else if (t === 'integer' || t === 'number') { ctl = el('input', {id:id, name:k, type:'number', step: t === 'integer' ? '1' : 'any', value:String(val)}); if (r.minimum !== undefined) ctl.min = r.minimum; if (r.maximum !== undefined) ctl.max = r.maximum; }
    else if (r['x-multiline']) { ctl = el('textarea', {id:id, name:k}); ctl.value = String(val); }
    else { ctl = el('input', {id:id, name:k, type: r.format === 'email' ? 'email' : 'text', value:String(val)}); if (r.maxLength) ctl.maxLength = r.maxLength; if (r.format === 'url') ctl.placeholder = 'example.com'; }
    ctl.dataset.type = t;
    f.appendChild(el('label', {for:id, text: (r.title || human(k)) + (req.indexOf(k) > -1 ? ' *' : '')}));
    f.appendChild(ctl);
    if (r.description) f.appendChild(el('div', {class:'hint', text:r.description}));
  });
  var go = el('button', {class:'btn', type:'submit', text:'Run skill'});
  f.appendChild(el('div', {class:'row'}, [go]));
  return f;
}
function currentInput(form, s){
  var body = {};
  if (!form) return body;
  form.querySelectorAll('[name]').forEach(function(c){
    if (c.type === 'checkbox') body[c.name] = c.checked;
    else if (c.value !== '') body[c.name] = (c.dataset.type === 'integer' || c.dataset.type === 'number') ? Number(c.value) : c.value;
  });
  return body;
}
function run(s, form, out){
  var body = currentInput(form, s), btn = form.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Running...';
  out.textContent = ''; out.appendChild(el('div', {class:'empty', text: s.network ? 'Reading the site. This can take a few seconds.' : 'Working...'}));
  fetch('api/skills/' + s.name, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)})
    .then(function(res){ return res.json().then(function(j){ return {ok: res.ok, j: j}; }); })
    .then(function(r){
      out.textContent = '';
      if (r.ok) { out.appendChild(results(r.j.result, s)); }
      else { out.appendChild(el('div', {class:'err', role:'alert', text:(r.j.errors || [r.j.error || 'Something went wrong.']).join(' ')})); }
      record(s.name, body, r.ok);
    })
    .catch(function(){ out.textContent = ''; out.appendChild(el('div', {class:'err', role:'alert', text:'The request failed. Check your connection and try again.'})); })
    .then(function(){ btn.disabled = false; btn.textContent = 'Run skill'; });
}
function record(name, input, ok){
  hist.unshift({skill:name, input:input, ok:ok, at:Date.now()});
  hist = hist.slice(0, 30); store('hist', hist);
}

/* ---------- result rendering ---------- */
function results(res, s){
  var wrap = el('div');
  if (res && typeof res.score === 'number' && res.grade && Array.isArray(res.checks)) wrap.appendChild(scorecard(res));
  var rest = {};
  Object.keys(res || {}).forEach(function(k){ if (!(res.checks && ['score','maxScore','grade','checks'].indexOf(k) > -1)) rest[k] = res[k]; });
  wrap.appendChild(value(rest));
  var raw = JSON.stringify(res, null, 2);
  wrap.appendChild(el('div', {class:'row'}, [
    copyBtn('Copy JSON', raw),
    el('button', {class:'btn ghost', type:'button', text:'Download JSON', onclick:function(){
      var a = el('a', {href:URL.createObjectURL(new Blob([raw], {type:'application/json'})), download:s.name + '.json'}); a.click(); setTimeout(function(){ URL.revokeObjectURL(a.href); }, 1000);
    }})
  ]));
  return wrap;
}
function scorecard(r){
  var pct = r.maxScore ? r.score / r.maxScore : 0;
  var col = pct >= .8 ? ['var(--okbg)','var(--ok)'] : pct >= .5 ? ['var(--warnbg)','var(--warn)'] : ['var(--badbg)','var(--bad)'];
  var box = el('div');
  box.appendChild(el('div', {class:'score'}, [
    el('div', {class:'ring', style:'background:' + col[0] + ';color:' + col[1], text:r.grade}),
    el('div', {}, [el('b', {text: r.score + ' / ' + r.maxScore, style:'font-size:1.4rem'}), el('div', {class:'hint', text:'Grade ' + r.grade})])
  ]));
  r.checks.forEach(function(c){
    box.appendChild(el('div', {class:'check'}, [
      el('span', {class:'pill ' + c.status, text:c.status}), el('b', {text:c.label}), el('span', {class:'hint', text:c.points + '/' + c.max}),
      c.detail ? el('div', {class:'d', text:c.detail}) : null,
      c.fix ? el('div', {class:'fix', text:'Fix: ' + c.fix}) : null
    ]));
  });
  return box;
}
function copyBtn(label, text){
  var b = el('button', {class:'btn ghost', type:'button', text:label});
  b.addEventListener('click', function(){
    var done = function(){ b.textContent = 'Copied'; setTimeout(function(){ b.textContent = label; }, 1400); };
    if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, function(){ b.textContent = 'Press Ctrl+C'; }); else b.textContent = 'Not supported';
  });
  return b;
}
function value(v){
  if (v === null || v === undefined || v === '') return el('span', {class:'hint', text:'—'});
  if (typeof v === 'boolean') return el('span', {text: v ? 'Yes' : 'No'});
  if (typeof v === 'number') return el('span', {text: Number.isInteger(v) ? v.toLocaleString() : String(Math.round(v * 100) / 100)});
  if (typeof v === 'string') {
    if (v.length > 300 || v.indexOf('\n') > -1) return el('div', {class:'code has-btn'}, [copyBtn('Copy', v), el('pre', {text:v})]);
    return /^https?:\/\//.test(v) ? el('a', {href:v, text:v, target:'_blank', rel:'noopener noreferrer'}) : el('span', {text:v});
  }
  if (Array.isArray(v)) {
    if (!v.length) return el('span', {class:'hint', text:'None'});
    if (v.every(function(x){ return x && typeof x === 'object' && !Array.isArray(x) && Object.keys(x).every(function(k){ return typeof x[k] !== 'object' || x[k] === null; }); })) return table(v);
    if (v.every(function(x){ return typeof x !== 'object'; })) { var ul = el('ul', {class:'plain'}); v.forEach(function(x){ ul.appendChild(el('li', {}, [value(x)])); }); return ul; }
    var d = el('div'); v.forEach(function(x){ d.appendChild(value(x)); }); return d;
  }
  var dl = el('dl', {class:'kv'});
  Object.keys(v).forEach(function(k){ dl.appendChild(el('dt', {text:human(k)})); dl.appendChild(el('dd', {}, [value(v[k])])); });
  return dl;
}
function table(rows){
  var cols = []; rows.forEach(function(r){ Object.keys(r).forEach(function(k){ if (cols.indexOf(k) < 0) cols.push(k); }); });
  var t = el('table'), head = el('tr');
  cols.forEach(function(c){ head.appendChild(el('th', {text:human(c)})); });
  t.appendChild(el('thead', {}, [head]));
  var body = el('tbody');
  rows.forEach(function(r){ var tr = el('tr'); cols.forEach(function(c){ tr.appendChild(el('td', {}, [value(r[c])])); }); body.appendChild(tr); });
  t.appendChild(body);
  return el('div', {class:'tablewrap'}, [t]);
}

function historyPage(){
  main.textContent = '';
  main.appendChild(el('h1', {text:'Recent runs'}));
  main.appendChild(el('p', {class:'lead', text:'Kept only in this browser. Open a run to load its inputs again.'}));
  var p = el('div', {class:'panel'});
  var shown = hist.filter(function(h){ return BY[h.skill]; });
  if (!shown.length) p.appendChild(el('div', {class:'empty', text:'Nothing yet. Run a skill and it will appear here.'}));
  shown.forEach(function(h){
    var open = el('a', {class:'btn ghost', href:'#/skill/' + h.skill, text:'Open', style:'text-decoration:none'});
    open.addEventListener('click', function(){ mem.prefill = h.input; });
    p.appendChild(el('div', {class:'hist'}, [el('div', {}, [el('b', {text:BY[h.skill].title}), el('br'), el('small', {text:when(h.at) + (h.ok ? '' : ' · had errors')})]), open]));
  });
  main.appendChild(p);
  if (shown.length) main.appendChild(el('div', {class:'row'}, [el('button', {class:'btn ghost', type:'button', text:'Clear history', onclick:function(){ hist = []; store('hist', hist); historyPage(); }})]));
}
function connectPage(){
  var b = D.base;
  main.textContent = '';
  main.appendChild(el('h1', {text:'Connect and API'}));
  main.appendChild(el('p', {class:'lead', text:'Every skill in this hub is also a tool you can reach from other software.'}));
  function block(title, text, body){ return el('div', {class:'panel', style:'margin-bottom:14px'}, [el('h3', {text:title, style:'margin:0 0 6px'}), el('p', {class:'hint', text:text}), el('div', {class:'code has-btn'}, [copyBtn('Copy', body), el('pre', {text:body})])]); }
  main.appendChild(block('MCP (for AI assistants)', 'Add this URL as a remote MCP server in a client that supports Streamable HTTP. A server card is published for discovery.', b + '/mcp'));
  main.appendChild(block('REST', 'Send the same inputs as the form, as JSON.', 'curl -X POST ' + b + '/api/skills/manual-work-estimator \\\n  -H "Content-Type: application/json" \\\n  -d \'{"teamSize":4,"hoursPerWeekEach":5,"hourlyCost":30}\''));
  main.appendChild(block('For agents', 'Plain-text index of every skill.', b + '/llms.txt'));
  main.appendChild(el('p', {class:'hint'}, ['Also: ', el('a', {href:b + '/api/skills', text:'/api/skills'}), ' and ', el('a', {href:b + '/.well-known/mcp/server-card.json', text:'server card'}), '.']));
}

function render(){
  var r = route();
  renderSide();
  if (r.page === 'app') appPage(r.arg);
  else if (r.page === 'skill') { var pre = mem.prefill; mem.prefill = null; skillPage(r.arg, pre); }
  else if (r.page === 'history') historyPage();
  else if (r.page === 'connect') connectPage();
  else home();
  window.scrollTo(0, 0);
}
document.getElementById('menu').addEventListener('click', function(){ var o = side.classList.toggle('open'); this.setAttribute('aria-expanded', String(o)); });
window.addEventListener('hashchange', render);
render();
})();
</script>
</body>
</html>
HTML;
}
