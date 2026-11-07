<?php
declare(strict_types=1);

return function (array $in): array {
    $parts = parse_url($in['url']);
    $host = strtolower($parts['host']);
    $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');

    $fetch = function (string $url, array $headers = []): array {
        try {
            return SafeFetch::get($url, $headers);
        } catch (RuntimeException $e) {
            return ['status' => 0, 'headers' => [], 'body' => '', 'url' => $url, 'truncated' => false];
        }
    };
    $meta = function (string $html): array {
        $title = preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m) ? trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5)) : '';
        $desc = '';
        if (preg_match('#<meta[^>]+name=["\']description["\'][^>]*content=["\']([^"\']*)#i', $html, $m)
            || preg_match('#<meta[^>]+content=["\']([^"\']*)["\'][^>]*name=["\']description["\']#i', $html, $m)) {
            $desc = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        }
        return [$title, $desc];
    };

    $home = $fetch($in['url']);
    if ($home['status'] !== 200) {
        throw new RuntimeException('Could not read the homepage (HTTP ' . $home['status'] . ').');
    }
    [$homeTitle, $homeDesc] = $meta($home['body']);

    // Find the sitemap
    $robots = $fetch($origin . '/robots.txt');
    $sitemapUrl = preg_match('/^\s*Sitemap\s*:\s*(\S+)/mi', $robots['body'], $m) ? $m[1] : $origin . '/sitemap.xml';
    $urls = [];
    $sitemap = $fetch($sitemapUrl);
    if ($sitemap['status'] !== 200 && $sitemapUrl !== $origin . '/sitemap.xml') {
        $sitemap = $fetch($origin . '/sitemap.xml'); // declared address unreachable, try the default
    }
    $bare = fn(string $h) => preg_replace('/^www\./', '', strtolower($h));
    $collect = function (array $res) use (&$urls, $host, $bare): void {
        if (preg_match_all('#<loc>\s*([^<\s]+)\s*</loc>#i', $res['body'], $m)) {
            foreach ($m[1] as $loc) {
                $loc = html_entity_decode($loc);
                if ($bare((string) parse_url($loc, PHP_URL_HOST)) === $bare($host)) {
                    $urls[$loc] = true;
                }
            }
        }
    };
    if ($sitemap['status'] === 200 && stripos($sitemap['body'], '<sitemapindex') !== false) {
        if (preg_match('#<loc>\s*([^<\s]+)\s*</loc>#i', $sitemap['body'], $m)) {
            $child = $fetch(html_entity_decode($m[1]));
            $collect($child);
        }
    } elseif ($sitemap['status'] === 200) {
        $collect($sitemap);
    }
    $urls = array_keys($urls);
    $notes = [];
    if (!$urls) {
        $notes[] = 'No sitemap found, so only the homepage is listed. Publish a sitemap.xml for a fuller file.';
    }
    $urls = array_slice($urls, 0, $in['maxLinks']);

    // Fetch real titles for the first few pages, label the rest from their path
    $titlesFetched = 0;
    $entries = [];
    foreach ($urls as $i => $u) {
        $label = '';
        $desc = '';
        if ($i < 8 && $u !== $in['url']) {
            $res = $fetch($u);
            if ($res['status'] === 200) {
                [$label, $desc] = $meta($res['body']);
                $titlesFetched++;
            }
        }
        if ($label === '') {
            $path = trim((string) parse_url($u, PHP_URL_PATH), '/');
            $slug = $path === '' ? 'Home' : basename($path);
            $label = ucfirst(trim(preg_replace('/[-_]+/', ' ', preg_replace('/\.\w+$/', '', $slug))));
        }
        $label = str_replace(['[', ']'], ['(', ')'], mb_substr($label, 0, 120));
        $first = explode('/', trim((string) parse_url($u, PHP_URL_PATH), '/'))[0] ?? '';
        $entries[] = ['url' => $u, 'label' => $label, 'desc' => mb_substr($desc, 0, 160), 'section' => count(explode('/', trim((string) parse_url($u, PHP_URL_PATH), '/'))) > 1 ? ucfirst(str_replace('-', ' ', $first)) : 'Pages'];
    }

    $name = $in['siteName'] ?? ($homeTitle !== '' ? preg_split('/\s[\/|\-\x{2013}\x{2014}]+\s/u', $homeTitle)[0] : $host);
    $summary = $in['summary'] ?? $homeDesc;
    $out = '# ' . trim($name) . "\n\n";
    if ($summary !== '') {
        $out .= '> ' . trim(preg_replace('/\s+/', ' ', $summary)) . "\n\n";
    } else {
        $notes[] = 'Add a one-line summary: the homepage has no meta description to borrow.';
    }
    $sections = [];
    foreach ($entries as $e) {
        $sections[$e['section']][] = $e;
    }
    uksort($sections, fn($a, $b) => $a === 'Pages' ? -1 : ($b === 'Pages' ? 1 : strcmp($a, $b)));
    foreach ($sections as $section => $items) {
        $out .= "## $section\n\n";
        foreach ($items as $e) {
            $out .= "- [{$e['label']}]({$e['url']})" . ($e['desc'] !== '' ? ': ' . $e['desc'] : '') . "\n";
        }
        $out .= "\n";
    }
    if (!$entries) {
        $out .= "## Pages\n\n- [Home]({$in['url']})\n";
    }
    return [
        'llmsTxt' => rtrim($out) . "\n",
        'pagesListed' => max(count($entries), 1),
        'titlesRead' => $titlesFetched,
        'notes' => array_merge($notes, ['Review the draft before publishing: link labels for pages beyond the first few come from their address, and descriptions are copied from page metadata.']),
    ];
};
