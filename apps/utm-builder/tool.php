<?php
declare(strict_types=1);

return function (array $in): array {
    $parts = parse_url($in['url']);
    $query = [];
    parse_str($parts['query'] ?? '', $query);
    foreach (['source', 'medium', 'campaign', 'term', 'content'] as $k) {
        if (($in[$k] ?? '') !== '') {
            $query['utm_' . $k] = trim(preg_replace('/\s+/', '-', mb_strtolower($in[$k])) ?? '');
        }
    }
    $url = $parts['scheme'] . '://' . (isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '')
        . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . ($parts['path'] ?? '/')
        . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    return ['url' => $url, 'note' => 'Values are lowercased and spaces become dashes so reports stay consistent.'];
};
