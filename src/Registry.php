<?php
declare(strict_types=1);

/**
 * Loads skills from skills/<name>/skill.json. Each skill folder holds:
 *   skill.json   name, title, description, category, inputSchema, optional network flag
 *   handler.php  returns function (array $input): array
 */
final class Registry
{
    public const CATEGORIES = [
        'agent' => 'AI agent tools',
        'business' => 'Business tools',
        'marketing' => 'Marketing and SEO',
        'developer' => 'Developer tools',
    ];

    /** @var array<string, array<string,mixed>> */
    private array $skills = [];

    public function __construct(private string $dir)
    {
        foreach (glob($dir . '/*/skill.json') ?: [] as $file) {
            $def = json_decode((string) file_get_contents($file), true);
            $folder = basename(dirname($file));
            if (!is_array($def) || ($def['name'] ?? '') !== $folder
                || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $folder)
                || !isset($def['title'], $def['description'], $def['category'], $def['inputSchema'])
                || !isset(self::CATEGORIES[$def['category']])
                || !is_file(dirname($file) . '/handler.php')) {
                continue; // skip anything malformed instead of breaking the whole app
            }
            if (!empty($def['network']) && !Env::flag('SKILLS_ENABLE_NETWORK', true)) {
                continue;
            }
            $this->skills[$folder] = $def;
        }
        ksort($this->skills);
    }

    /** @return array<string, array<string,mixed>> */
    public function all(): array
    {
        return $this->skills;
    }

    /** @return ?array<string,mixed> */
    public function get(string $name): ?array
    {
        return $this->skills[$name] ?? null;
    }

    /**
     * Validates input and runs the skill.
     *
     * @return array{ok: bool, result?: array<string,mixed>, errors?: list<string>, error?: string}
     */
    public function run(string $name, array $input): array
    {
        $def = $this->get($name);
        if (!$def) {
            return ['ok' => false, 'errors' => ["Unknown skill: $name"]];
        }
        [$clean, $errors] = Validator::check($def['inputSchema'], $input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $handler = require $this->dir . '/' . $name . '/handler.php';
        try {
            return ['ok' => true, 'result' => $handler($clean)];
        } catch (RuntimeException $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }
}
