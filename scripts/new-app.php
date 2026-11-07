<?php
declare(strict_types=1);

// Create a new browser app (and optional agent twin) in apps/<name>/.
// Usage: php scripts/new-app.php my-tool "My tool" business [--agent]
if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/new-app.php <name> \"<Title>\" <agent|business|marketing|developer> [--agent]\n");
    exit(1);
}
[, $name, $title, $category] = $argv;
$agent = in_array('--agent', $argv, true);
if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $name)) {
    fwrite(STDERR, "Name must be lowercase letters, numbers and single dashes.\n");
    exit(1);
}
if (!in_array($category, ['agent', 'business', 'marketing', 'developer'], true)) {
    fwrite(STDERR, "Category must be agent, business, marketing or developer.\n");
    exit(1);
}
$dir = dirname(__DIR__) . '/apps/' . $name;
if (is_dir($dir)) {
    fwrite(STDERR, "apps/$name already exists.\n");
    exit(1);
}
mkdir($dir, 0775, true);
$manifest = ['name' => $name, 'title' => $title, 'description' => 'One sentence on what it does and who it is for.', 'category' => $category];
if ($agent) {
    $manifest['inputSchema'] = ['type' => 'object', 'properties' => ['text' => ['type' => 'string', 'title' => 'Text', 'maxLength' => 2000]], 'required' => ['text']];
}
file_put_contents("$dir/app.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
file_put_contents("$dir/app.js", <<<JS
CS.apps['$name'] = function (root) {
  var el = CS.el;
  var form = el('div', { class: 'panel' }), out = el('div', { class: 'panel' }), result = CS.out('');
  CS.add(form, 'Text', CS.area('', function (v) { result.set(v.toUpperCase()); }, 5));
  CS.add(out, 'Result', result);
  root.appendChild(el('div', { class: 'cols' }, [form, out]));
};

JS);
if ($agent) {
    file_put_contents("$dir/tool.php", <<<'PHP'
<?php
declare(strict_types=1);

// Same job as app.js, for AI agents (MCP) and the REST API. Throw RuntimeException for problems the user can fix.
return function (array $in): array {
    return ['result' => mb_strtoupper($in['text'])];
};

PHP);
}
echo "Created apps/$name. Start the server (php -S 127.0.0.1:8080 index.php) and open http://127.0.0.1:8080/#/app/$name\n";
