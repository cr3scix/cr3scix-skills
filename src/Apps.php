<?php
declare(strict_types=1);

/**
 * Interactive browser apps. Each lives in apps/<name>/ with:
 *   app.json  name, title, description, category
 *   app.js    registers itself: CS.apps['<name>'] = function (root) { ... }
 * They run entirely in the visitor's browser, so nothing is sent to the server.
 */
final class Apps
{
    /** @var array<string, array<string,string>> */
    private array $apps = [];

    public function __construct(private string $dir)
    {
        foreach (glob($dir . '/*/app.json') ?: [] as $file) {
            $def = json_decode((string) file_get_contents($file), true);
            $folder = basename(dirname($file));
            if (!is_array($def) || ($def['name'] ?? '') !== $folder
                || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $folder)
                || !isset($def['title'], $def['description'], $def['category'])
                || !isset(Registry::CATEGORIES[$def['category']])
                || !is_file(dirname($file) . '/app.js')) {
                continue;
            }
            $this->apps[$folder] = ['title' => (string) $def['title'], 'description' => (string) $def['description'], 'category' => (string) $def['category']];
        }
        ksort($this->apps);
    }

    /** @return array<string, array<string,string>> */
    public function all(): array
    {
        return $this->apps;
    }

    public function source(string $name): ?string
    {
        return isset($this->apps[$name]) ? (string) file_get_contents($this->dir . '/' . $name . '/app.js') : null;
    }
}
