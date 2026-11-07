<?php
declare(strict_types=1);

final class Http
{
    public const MAX_BODY = 65536;

    public static function json(mixed $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        self::cors();
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function text(string $body, string $type = 'text/plain', int $status = 200): never
    {
        http_response_code($status);
        header("Content-Type: $type; charset=utf-8");
        header('X-Content-Type-Options: nosniff');
        self::cors();
        echo $body;
        exit;
    }

    public static function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, Mcp-Session-Id, MCP-Protocol-Version');
    }

    /** Reads and decodes a JSON request body. Returns null if it is missing, too large or not JSON. */
    public static function body(): mixed
    {
        $raw = file_get_contents('php://input', false, null, 0, self::MAX_BODY + 1);
        if ($raw === false || $raw === '' || strlen($raw) > self::MAX_BODY) {
            return null;
        }
        return json_decode($raw, true);
    }

    public static function baseUrl(): string
    {
        $configured = Env::get('SKILLS_BASE_URL');
        if ($configured) {
            return rtrim($configured, '/');
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $host)) {
            $host = 'localhost';
        }
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
        $dir = str_ends_with($script, '.php') ? rtrim(str_replace('\\', '/', dirname($script)), '/') : '';
        return ($https ? 'https' : 'http') . '://' . $host . $dir;
    }

    public static function clientIp(): string
    {
        // Only trust the Cloudflare header when you really are behind Cloudflare.
        if (Env::flag('SKILLS_TRUST_CLOUDFLARE', false) && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Simple per-IP limiter backed by small files in the system temp folder.
     * Returns true when the call is allowed.
     */
    public static function allow(string $bucket, int $limit, int $windowSeconds): bool
    {
        if ($limit <= 0) {
            return true;
        }
        $dir = sys_get_temp_dir() . '/cr3scix-skills-rl';
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            return true; // fail open rather than break the app
        }
        $file = $dir . '/' . hash('sha256', $bucket . '|' . self::clientIp()) . '.json';
        $fh = @fopen($file, 'c+');
        if (!$fh) {
            return true;
        }
        flock($fh, LOCK_EX);
        $now = time();
        $hits = json_decode((string) stream_get_contents($fh), true);
        $hits = is_array($hits) ? array_values(array_filter($hits, fn($t) => is_int($t) && $t > $now - $windowSeconds)) : [];
        $allowed = count($hits) < $limit;
        if ($allowed) {
            $hits[] = $now;
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($hits));
        flock($fh, LOCK_UN);
        fclose($fh);
        return $allowed;
    }
}
