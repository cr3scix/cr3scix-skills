<?php
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

$registry = new Registry(__DIR__ . '/skills', __DIR__ . '/apps');
$apps = new Apps(__DIR__ . '/apps');
$mcp = new Mcp($registry);
$base = Http::baseUrl();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
$prefix = str_ends_with($script, '.php') ? rtrim(str_replace('\\', '/', dirname($script)), '/') : '';
if ($prefix !== '' && str_starts_with($path, $prefix)) {
    $path = substr($path, strlen($prefix)) ?: '/';
}
$path = '/' . trim($path, '/');

if ($method === 'OPTIONS') {
    http_response_code(204);
    Http::cors();
    exit;
}

// ---- Catalog page ---------------------------------------------------------
if ($path === '/' && $method === 'GET') {
    Http::text(Page::catalog($registry, $base, $apps), 'text/html');
}

// ---- Browser apps (JavaScript served from apps/<name>/app.js) -------------------
if (preg_match('#^/apps/([a-z0-9-]+)\.js$#', $path, $am) && $method === 'GET') {
    $src = $apps->source($am[1]);
    if ($src === null) {
        Http::json(['error' => 'Unknown app'], 404);
    }
    header('Cache-Control: no-cache');
    Http::text($src, 'application/javascript');
}

if (preg_match('#^/apps/([a-z0-9-]+)/vendor/([a-z0-9][a-z0-9.\-]*\.js)$#', $path, $vm) && $method === 'GET' && isset($apps->all()[$vm[1]])) {
    $file = __DIR__ . '/apps/' . $vm[1] . '/vendor/' . $vm[2];
    if (!is_file($file)) {
        Http::json(['error' => 'Not found'], 404);
    }
    header('Cache-Control: public, max-age=604800');
    Http::text((string) file_get_contents($file), 'application/javascript');
}

// ---- REST API -------------------------------------------------------------
if ($path === '/api/skills' && $method === 'GET') {
    $list = [];
    foreach ($registry->all() as $name => $def) {
        $list[] = ['name' => $name, 'title' => $def['title'], 'description' => $def['description'], 'category' => $def['category'], 'kind' => empty($def['app']) ? 'skill' : 'app'];
    }
    Http::json(['skills' => $list]);
}

if (preg_match('#^/api/skills/([a-z0-9-]+)$#', $path, $m)) {
    $def = $registry->get($m[1]);
    if (!$def) {
        Http::json(['error' => 'Unknown skill'], 404);
    }
    if ($method === 'GET') {
        Http::json($def);
    }
    if ($method === 'POST') {
        if (!Http::allow('api', Env::int('SKILLS_RATE_GENERAL', 60), 60)) {
            Http::json(['error' => 'Rate limit reached. Try again in a minute.'], 429);
        }
        if (!empty($def['network']) && !Http::allow('network', Env::int('SKILLS_RATE_NETWORK', 10), 60)) {
            Http::json(['error' => 'Rate limit reached for skills that fetch web pages. Try again in a minute.'], 429);
        }
        $body = Http::body();
        if (!is_array($body)) {
            Http::json(['error' => 'Send a JSON object as the request body.'], 400);
        }
        $run = $registry->run($m[1], $body);
        $run['ok'] ? Http::json(['result' => $run['result']]) : Http::json(['errors' => $run['errors']], 422);
    }
    Http::json(['error' => 'Method not allowed'], 405);
}

// ---- MCP ------------------------------------------------------------------
if ($path === '/mcp') {
    if ($method !== 'POST') {
        header('Allow: POST, OPTIONS');
        Http::json(['error' => 'This is an MCP endpoint. Send JSON-RPC 2.0 messages with POST.'], 405);
    }
    if (!Http::allow('mcp', Env::int('SKILLS_RATE_GENERAL', 60), 60)) {
        Http::json(Mcp::error(null, -32000, 'Rate limit reached'), 429);
    }
    $message = Http::body();
    if (!is_array($message)) {
        Http::json(Mcp::error(null, -32700, 'Parse error'), 400);
    }
    $isBatch = $message !== [] && $message === array_values($message);
    $responses = [];
    foreach ($isBatch ? $message : [$message] as $item) {
        $response = $mcp->handle($item);
        if ($response !== null) {
            $responses[] = $response;
        }
    }
    if (!$responses) {
        http_response_code(202);
        Http::cors();
        exit;
    }
    Http::json($isBatch ? $responses : $responses[0]);
}

if ($path === '/.well-known/mcp/server-card.json' && $method === 'GET') {
    Http::json($mcp->serverCard($base));
}

// ---- Discovery files ------------------------------------------------------
if ($path === '/llms.txt' && $method === 'GET') {
    Http::text(Page::llms($registry, $base, $apps));
}

if ($path === '/robots.txt' && $method === 'GET') {
    Http::text("User-agent: *\nContent-Signal: search=yes, ai-input=yes, ai-train=no\nAllow: /\n");
}

Http::json(['error' => 'Not found'], 404);
