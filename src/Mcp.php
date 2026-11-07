<?php
declare(strict_types=1);

/** Minimal MCP server (Streamable HTTP, JSON responses, no sessions) exposing every skill as a tool. */
final class Mcp
{
    public const SUPPORTED_VERSIONS = ['2025-06-18', '2025-03-26', '2024-11-05'];

    public function __construct(private Registry $registry)
    {
    }

    /** @return ?array<string,mixed> null when no response is due (notifications) */
    public function handle(mixed $msg): ?array
    {
        if (!is_array($msg) || ($msg['jsonrpc'] ?? null) !== '2.0') {
            return self::error(null, -32600, 'Invalid Request');
        }
        $method = $msg['method'] ?? null;
        if (!is_string($method) || !array_key_exists('id', $msg)) {
            return null; // notification or a response from the client
        }
        $id = $msg['id'];
        $params = is_array($msg['params'] ?? null) ? $msg['params'] : [];

        switch ($method) {
            case 'initialize':
                $asked = $params['protocolVersion'] ?? '';
                $version = in_array($asked, self::SUPPORTED_VERSIONS, true) ? $asked : self::SUPPORTED_VERSIONS[0];
                return self::ok($id, [
                    'protocolVersion' => $version,
                    'capabilities' => ['tools' => ['listChanged' => false]],
                    'serverInfo' => ['name' => 'cr3scix-skills', 'title' => 'Cr3scix Skills', 'version' => SKILLS_VERSION],
                    'instructions' => 'Each tool is a self-contained skill. Tools that fetch web pages only reach public sites.',
                ]);
            case 'ping':
                return self::ok($id, new stdClass());
            case 'tools/list':
                return self::ok($id, ['tools' => $this->tools()]);
            case 'tools/call':
                $name = is_string($params['name'] ?? null) ? $params['name'] : '';
                if (!$this->registry->get($name)) {
                    return self::error($id, -32602, "Unknown tool: $name");
                }
                $args = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];
                $def = $this->registry->get($name);
                if (!empty($def['network']) && !Http::allow('network', Env::int('SKILLS_RATE_NETWORK', 10), 60)) {
                    return self::ok($id, self::toolText('Rate limit reached. Try again in a minute.', true));
                }
                $run = $this->registry->run($name, $args);
                if (!$run['ok']) {
                    return self::ok($id, self::toolText(implode('; ', $run['errors'] ?? ['Failed']), true));
                }
                return self::ok($id, self::toolText($run['result'], false));
            default:
                return self::error($id, -32601, 'Method not found');
        }
    }

    /** @return list<array<string,mixed>> */
    public function tools(): array
    {
        $tools = [];
        foreach ($this->registry->all() as $name => $def) {
            $schema = $def['inputSchema'];
            if (empty($schema['properties'])) {
                $schema['properties'] = new stdClass();
            }
            $schema['type'] = 'object';
            $tools[] = [
                'name' => $name,
                'title' => $def['title'],
                'description' => $def['description'],
                'inputSchema' => $schema,
                'annotations' => [
                    'readOnlyHint' => true,
                    'destructiveHint' => false,
                    'idempotentHint' => true,
                    'openWorldHint' => !empty($def['network']),
                ],
            ];
        }
        return $tools;
    }

    /** @return array<string,mixed> */
    public function serverCard(string $baseUrl): array
    {
        return [
            '$schema' => 'https://static.modelcontextprotocol.io/schemas/mcp-server-card/v1.json',
            'version' => '1.0',
            'protocolVersion' => self::SUPPORTED_VERSIONS[0],
            'name' => 'cr3scix-skills',
            'title' => 'Cr3scix Skills',
            'description' => 'Open-source collection of skills for businesses and developers, exposed as MCP tools.',
            'serverInfo' => ['name' => 'cr3scix-skills', 'title' => 'Cr3scix Skills', 'version' => SKILLS_VERSION],
            'endpoint' => $baseUrl . '/mcp',
            'transport' => ['type' => 'streamable-http', 'endpoint' => $baseUrl . '/mcp'],
            'capabilities' => ['tools' => ['listChanged' => false]],
            'authentication' => ['required' => false],
            'documentationUrl' => $baseUrl . '/llms.txt',
        ];
    }

    private static function toolText(mixed $payload, bool $isError): array
    {
        $text = is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $result = ['content' => [['type' => 'text', 'text' => $text]], 'isError' => $isError];
        if (!$isError && is_array($payload)) {
            $result['structuredContent'] = $payload;
        }
        return $result;
    }

    private static function ok(mixed $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    public static function error(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
