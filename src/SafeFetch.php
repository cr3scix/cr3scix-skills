<?php
declare(strict_types=1);

/**
 * Fetches public web pages on behalf of a visitor without letting them reach
 * internal services (SSRF protection):
 *  - http(s) only, ports 80 and 443 only
 *  - the host is resolved once, every address must be public, and the request
 *    is pinned to that address so DNS cannot change between check and fetch
 *  - redirects are followed by hand and re-checked at every hop
 *  - response size and time are capped
 * IPv4 only. Hosts that only have IPv6 addresses are refused.
 *
 * For local testing set SKILLS_ALLOW_PRIVATE_HOSTS=1 (never in production).
 */
final class SafeFetch
{
    public const USER_AGENT = 'Mozilla/5.0 (compatible; Cr3scixSkills/' . SKILLS_VERSION . '; +https://github.com/cr3scix/cr3scix-skills)';

    /**
     * @param array<string,string> $headers extra request headers
     * @return array{status:int, headers:array<string,string>, body:string, url:string, truncated:bool}
     */
    public static function get(string $url, array $headers = [], int $maxBytes = 1048576, int $maxRedirects = 3): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP curl extension is required.');
        }
        for ($hop = 0; ; $hop++) {
            $parts = parse_url($url);
            if (!$parts || empty($parts['host']) || !isset($parts['scheme'])
                || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
                throw new RuntimeException('Only http and https URLs are allowed.');
            }
            if (isset($parts['user']) || isset($parts['pass'])) {
                throw new RuntimeException('URLs with credentials are not allowed.');
            }
            $scheme = strtolower($parts['scheme']);
            $host = strtolower($parts['host']);
            $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
            $allowPrivate = Env::flag('SKILLS_ALLOW_PRIVATE_HOSTS', false);
            if (!$allowPrivate && !in_array($port, [80, 443], true)) {
                throw new RuntimeException('Only ports 80 and 443 are allowed.');
            }
            $ip = self::resolve($host, $allowPrivate);

            $responseHeaders = [];
            $body = '';
            $truncated = false;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RESOLVE => ["$host:$port:$ip"],
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_USERAGENT => self::USER_AGENT,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => array_map(
                    fn($k, $v) => "$k: $v",
                    array_keys($headers),
                    array_values($headers)
                ),
                CURLOPT_HEADERFUNCTION => function ($c, string $line) use (&$responseHeaders) {
                    $pos = strpos($line, ':');
                    if ($pos !== false) {
                        $name = strtolower(trim(substr($line, 0, $pos)));
                        $value = trim(substr($line, $pos + 1));
                        $responseHeaders[$name] = isset($responseHeaders[$name])
                            ? $responseHeaders[$name] . ', ' . $value
                            : $value;
                    }
                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION => function ($c, string $chunk) use (&$body, &$truncated, $maxBytes) {
                    $room = $maxBytes - strlen($body);
                    if (strlen($chunk) > $room) {
                        $body .= substr($chunk, 0, max(0, $room));
                        $truncated = true;
                        return 0; // stop the transfer
                    }
                    $body .= $chunk;
                    return strlen($chunk);
                },
            ]);
            curl_exec($ch);
            $errno = curl_errno($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($errno !== 0 && !($truncated && $errno === CURLE_WRITE_ERROR)) {
                throw new RuntimeException("Could not fetch $host: $error");
            }

            if ($status >= 300 && $status < 400 && !empty($responseHeaders['location'])) {
                if ($hop >= $maxRedirects) {
                    throw new RuntimeException('Too many redirects.');
                }
                $url = self::absolute($url, $responseHeaders['location']);
                continue;
            }
            return [
                'status' => $status,
                'headers' => $responseHeaders,
                'body' => $body,
                'url' => $url,
                'truncated' => $truncated,
            ];
        }
    }

    public static function resolve(string $host, bool $allowPrivate = false): string
    {
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ips = [$host];
        } elseif (filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new RuntimeException('IPv6 addresses are not supported.');
        } else {
            $records = @dns_get_record($host, DNS_A);
            foreach ($records ?: [] as $record) {
                if (!empty($record['ip'])) {
                    $ips[] = $record['ip'];
                }
            }
        }
        if (!$ips) {
            throw new RuntimeException("Could not find an IPv4 address for $host.");
        }
        foreach ($ips as $ip) {
            if (!$allowPrivate && !self::isPublic($ip)) {
                throw new RuntimeException('That address is not a public internet address.');
            }
        }
        return $ips[0];
    }

    public static function isPublic(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        $long = ip2long($ip);
        // Carrier-grade NAT 100.64.0.0/10 and benchmarking 198.18.0.0/15 are not covered by the PHP flags on all versions.
        foreach ([['100.64.0.0', 10], ['198.18.0.0', 15], ['192.0.0.0', 24], ['192.88.99.0', 24]] as [$net, $bits]) {
            $mask = -1 << (32 - $bits);
            if (($long & $mask) === (ip2long($net) & $mask)) {
                return false;
            }
        }
        return true;
    }

    private static function absolute(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }
        $p = parse_url($base);
        $origin = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        if (str_starts_with($location, '//')) {
            return $p['scheme'] . ':' . $location;
        }
        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }
        $dir = isset($p['path']) ? preg_replace('#/[^/]*$#', '/', $p['path']) : '/';
        return $origin . $dir . $location;
    }
}
