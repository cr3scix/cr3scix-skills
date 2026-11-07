<?php
declare(strict_types=1);

// Run with: php tests/run.php   (no network needed)
putenv('SKILLS_RATE_DIR=' . sys_get_temp_dir() . '/skills-test-' . getmypid());
require __DIR__ . '/../src/bootstrap.php';

$pass = 0;
$fail = 0;
function check(string $name, bool $ok, string $note = ''): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? "  ok   " : "  FAIL ") . $name . ($ok || $note === '' ? '' : " ($note)") . "\n";
}

$registry = new Registry(__DIR__ . '/../skills', __DIR__ . '/../apps');
check('agent tools load (skills plus app twins)', count($registry->all()) >= 26, (string) count($registry->all()));

// Every skill is well formed
foreach ($registry->all() as $name => $def) {
    check("$name has object schema", ($def['inputSchema']['type'] ?? '') === 'object');
}

// Validation
$bad = $registry->run('manual-work-estimator', ['teamSize' => 0]);
check('rejects out-of-range and missing input', !$bad['ok'] && count($bad['errors']) >= 2);
$bad = $registry->run('nope', []);
check('unknown skill is an error', !$bad['ok']);

// Estimator maths: 4 people x 5h x $30 x 48 weeks = 28,800 ; 60% automatable
$run = $registry->run('manual-work-estimator', ['teamSize' => 4, 'hoursPerWeekEach' => 5, 'hourlyCost' => 30, 'buildCost' => 5000]);
check('estimator runs', $run['ok'], json_encode($run));
$flat = json_encode($run['result']);
check('estimator reports yearly cost 28800', str_contains($flat, '28800'), $flat);
check('estimator reports 17280 saving', str_contains($flat, '17280'), $flat);

// JSON-LD
$run = $registry->run('jsonld-generator', ['name' => 'Acme Ltd', 'url' => 'acme.example']);
check('jsonld runs', $run['ok'], json_encode($run));
check('jsonld script tag is valid', $run['ok'] && str_contains($run['result']['scriptTag'], 'application/ld+json'));

// SSRF guard blocks private targets
foreach (['http://127.0.0.1/', 'http://169.254.169.254/latest/meta-data/', 'http://localhost/', 'http://[::1]/', 'http://10.0.0.1/', 'file:///etc/passwd'] as $u) {
    $blocked = false;
    try {
        SafeFetch::get($u);
    } catch (RuntimeException $e) {
        $blocked = true;
    }
    check("SafeFetch blocks $u", $blocked);
}

// MCP
$mcp = new Mcp($registry);
$init = $mcp->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => ['protocolVersion' => '2025-06-18']]);
check('mcp initialize', ($init['result']['serverInfo']['name'] ?? '') === 'cr3scix-toolkit');
$list = $mcp->handle(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list']);
check('mcp lists every skill', count($list['result']['tools']) === count($registry->all()));
$call = $mcp->handle(['jsonrpc' => '2.0', 'id' => 3, 'method' => 'tools/call', 'params' => ['name' => 'manual-work-estimator', 'arguments' => ['teamSize' => 2, 'hoursPerWeekEach' => 3, 'hourlyCost' => 20]]]);
check('mcp tool call succeeds', ($call['result']['isError'] ?? true) === false);
$call = $mcp->handle(['jsonrpc' => '2.0', 'id' => 4, 'method' => 'tools/call', 'params' => ['name' => 'manual-work-estimator', 'arguments' => []]]);
check('mcp reports bad input as tool error', ($call['result']['isError'] ?? false) === true);
check('mcp notification gets no reply', $mcp->handle(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']) === null);
check('mcp unknown method errors', ($mcp->handle(['jsonrpc' => '2.0', 'id' => 5, 'method' => 'x'])['error']['code'] ?? 0) === -32601);

// Apps
$apps = new Apps(__DIR__ . '/../apps');
check('apps load', count($apps->all()) >= 27, (string) count($apps->all()));
foreach ($apps->all() as $name => $def) {
    $src = (string) $apps->source($name);
    check("app $name registers itself", str_contains($src, "CS.apps['$name']"));
}
check('unknown app has no source', $apps->source('../etc/passwd') === null);

// App twins give the same answers people see in the browser
$run = fn($n, $i) => $registry->run($n, $i);
$r = $run('json-tools', ['json' => '{"b":1,"a":{}}', 'action' => 'sort', 'indent' => 2]);
check('json twin sorts keys and keeps empty objects', ($r['result']['result'] ?? '') === "{\n  \"a\": {},\n  \"b\": 1\n}", json_encode($r));
check('json twin reports invalid JSON', ($run('json-tools', ['json' => '{"a":'])['result']['valid'] ?? true) === false);
check('base64 twin round-trips UTF-8', ($run('encode-decode', ['text' => $run('encode-decode', ['text' => 'héllo ✓', 'operation' => 'base64-encode'])['result']['result'], 'operation' => 'base64-decode'])['result']['result'] ?? '') === 'héllo ✓');
check('uuid twin makes v4 UUIDs', (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $run('id-generator', ['type' => 'uuid', 'count' => 1])['result']['values'][0] ?? ''));
check('password twin respects length', strlen($run('id-generator', ['length' => 33, 'count' => 1])['result']['values'][0] ?? '') === 33);
$r = $run('margin-calculator', ['unitCost' => 40, 'price' => 100, 'fixedCosts' => 5000]);
check('margin twin maths', ($r['result']['marginPercent'] ?? 0) == 60 && ($r['result']['breakEvenUnits'] ?? 0) === 84);
check('utm twin keeps existing query', ($run('utm-builder', ['url' => 'example.com/p?x=1', 'source' => 'News', 'medium' => 'email', 'campaign' => 'Spring Sale'])['result']['url'] ?? '') === 'https://example.com/p?x=1&utm_source=news&utm_medium=email&utm_campaign=spring-sale');
check('slug twin strips accents', ($run('text-toolkit', ['text' => 'Crème Brûlée!', 'transform' => 'slug'])['result']['result'] ?? '') === 'creme-brulee');
check('seo twin escapes HTML', str_contains($run('seo-meta-preview', ['title' => 'A <b>', 'description' => 'x'])['result']['tags'] ?? '', 'A &lt;b&gt;'));
check('robots twin blocks training bots by default', str_contains($run('robots-builder', [])['result']['robotsTxt'] ?? '', "User-agent: GPTBot\nDisallow: /"));
check('regex twin finds groups', ($run('regex-tester', ['pattern' => '(\\w+)@', 'text' => 'a@ b@'])['result']['count'] ?? 0) === 2);
check('regex twin survives catastrophic patterns', !$run('regex-tester', ['pattern' => '(a+)+$', 'text' => str_repeat('a', 40) . 'b'])['ok']);
check('invoice twin totals with tax', ($run('invoice-maker', ['items' => "A | 2 | 50\nB | 1 | 100", 'taxPercent' => 18])['result']['total'] ?? 0) == 236);
check('whatsapp twin builds wa.me link', ($run('whatsapp-link', ['phone' => '+91 98765 43210', 'message' => 'Hi & hello'])['result']['url'] ?? '') === 'https://wa.me/919876543210?text=Hi%20%26%20hello');
check('gst twin removes 18% GST', ($run('gst-calculator', ['amount' => 1180, 'mode' => 'remove'])['result']['cgst'] ?? 0) == 90);
check('loan twin EMI', ($run('loan-calculator', ['principal' => 100000, 'annualRatePercent' => 12, 'months' => 12])['result']['emi'] ?? 0) == 8884.88);
check('contrast twin matches WCAG example', ($run('contrast-checker', ['foreground' => '#777', 'background' => '#fff'])['result']['ratio'] ?? 0) == 4.47);
check('csv twin handles quoted commas', ($run('csv-json', ['input' => "a,b\n\"x, y\",2"])['result']['json'][0]['a'] ?? '') === 'x, y');
check('csv twin defuses spreadsheet formulas', str_contains($run('csv-json', ['input' => '[{"a":"=1+1"}]', 'direction' => 'json-to-csv'])['result']['csv'] ?? '', "'=1+1"));
check('timestamp twin', ($run('timestamp-converter', ['value' => '1735689600'])['result']['iso8601Utc'] ?? '') === '2025-01-01T00:00:00Z');
check('timezone twin', ($run('timezone-planner', ['baseZone' => 'Asia/Kolkata', 'dateTime' => '2026-11-10 10:00', 'zones' => 'UTC'])['result']['utc'] ?? '') === '2026-11-10T04:30:00+00:00');
check('diff twin', ($run('text-diff', ['original' => "a\nb", 'changed' => "a\nc"])['result']['diff'] ?? '') === "  a\n- b\n+ c");
check('signature twin escapes names', str_contains($run('email-signature', ['name' => 'A<b>'])['result']['html'] ?? '', 'A&lt;b&gt;'));
$r = $run('social-post-checker', ['text' => str_repeat('a', 270) . ' https://example.com/long/url', 'platforms' => 'x']);
check('post checker counts URLs as 23 on X', ($r['result']['results'][0]['length'] ?? 0) === 294, json_encode($r['result']['results'][0] ?? null));
check('post checker flags placeholders', !($run('social-post-checker', ['text' => 'Hi {{name}}', 'platforms' => 'bluesky'])['result']['readyEverywhere'] ?? true));

// The agent skill's Python checker uses the same limits as the browser app
$py = (string) @file_get_contents(__DIR__ . '/../agent-skills/skills/social-media/scripts/check_post.py');
$js = (string) file_get_contents(__DIR__ . '/../apps/social-post-checker/vendor/platforms.js');
preg_match_all('/"([a-z-]+)":\s*\{"name": "[^"]+",\s*"limit": (\d+)/', $py, $pm);
preg_match_all('/"([a-z-]+)": \{"name": "[^"]+", "limit": (\d+)/', $js, $jm);
check('platform limits match between skill and app', $pm[1] && array_combine($pm[1], $pm[2]) == array_combine($jm[1], $jm[2]));

// Pages
$html = Page::catalog($registry, 'https://example.test', $apps);
check('dashboard renders and embeds skills', str_contains($html, 'manual-work-estimator') && str_contains($html, 'id="data"'));
check('dashboard data block cannot break out', substr_count($html, '</script>') === 2);
check('llms.txt lists skills', str_contains(Page::llms($registry, 'https://example.test', $apps), '/api/skills/jsonld-generator'));

echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
