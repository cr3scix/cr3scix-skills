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

$registry = new Registry(__DIR__ . '/../skills');
check('skills load', count($registry->all()) >= 4, (string) count($registry->all()));

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
check('mcp initialize', ($init['result']['serverInfo']['name'] ?? '') === 'cr3scix-skills');
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
check('apps load', count($apps->all()) >= 11, (string) count($apps->all()));
foreach ($apps->all() as $name => $def) {
    $src = (string) $apps->source($name);
    check("app $name registers itself", str_contains($src, "CS.apps['$name']"));
}
check('unknown app has no source', $apps->source('../etc/passwd') === null);

// Pages
$html = Page::catalog($registry, 'https://example.test', $apps);
check('dashboard renders and embeds skills', str_contains($html, 'manual-work-estimator') && str_contains($html, 'id="data"'));
check('dashboard data block cannot break out', substr_count($html, '</script>') === 2);
check('llms.txt lists skills', str_contains(Page::llms($registry, 'https://example.test', $apps), '/api/skills/jsonld-generator'));

echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
