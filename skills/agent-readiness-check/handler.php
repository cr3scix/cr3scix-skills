<?php
declare(strict_types=1);

/**
 * A heuristic checklist, not an official standard or anyone's certification.
 * Each check is independent so one failed fetch never hides the rest.
 */
return function (array $in): array {
    $parts = parse_url($in['url']);
    $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    $checks = [];
    $add = function (string $id, string $label, int $max, bool|float $earned, string $detail, string $fix = '') use (&$checks): void {
        $points = is_bool($earned) ? ($earned ? $max : 0) : (int) round($max * $earned);
        $status = $points >= $max ? 'pass' : ($points > 0 ? 'partial' : 'fail');
        $checks[] = ['id' => $id, 'label' => $label, 'status' => $status, 'points' => $points, 'max' => $max, 'detail' => $detail] + ($status !== 'pass' && $fix !== '' ? ['fix' => $fix] : []);
    };
    $try = function (string $url, array $headers = []): ?array {
        try {
            return SafeFetch::get($url, $headers);
        } catch (RuntimeException $e) {
            return ['status' => 0, 'headers' => [], 'body' => '', 'url' => $url, 'truncated' => false, 'error' => $e->getMessage()];
        }
    };

    // ---- Homepage ----------------------------------------------------------
    $home = $try($in['url'], ['Accept' => 'text/html,application/xhtml+xml']);
    $html = $home['status'] === 200 ? $home['body'] : '';
    $blocked = in_array($home['status'], [401, 403, 429, 503], true);
    $add('homepage_reachable', 'Homepage reachable for an automated visitor', 10, $home['status'] === 200,
        $home['status'] === 200 ? 'Returned HTTP 200.' : ($home['status'] ? "Returned HTTP {$home['status']}." : 'Could not connect: ' . ($home['error'] ?? 'unknown error')),
        $blocked ? 'Your bot protection or firewall is turning automated visitors away. Allow known AI agent user agents.' : 'Make sure the homepage loads for plain HTTP requests.');

    // ---- robots.txt --------------------------------------------------------
    $robots = $try($origin . '/robots.txt');
    $robotsText = $robots['status'] === 200 ? $robots['body'] : '';
    $add('robots_txt', 'robots.txt present', 5, $robotsText !== '', $robotsText !== '' ? 'Found.' : 'Not found.', 'Publish a robots.txt at the top of the site.');
    $add('content_signal', 'Content-Signal declared', 5, (bool) preg_match('/^\s*Content-Signal\s*:/mi', $robotsText),
        preg_match('/^\s*Content-Signal\s*:\s*(.+)$/mi', $robotsText, $m) ? trim($m[1]) : 'No Content-Signal line.',
        'Add a line such as "Content-Signal: search=yes, ai-train=no" to say how your content may be used.');
    $add('sitemap_declared', 'Sitemap declared in robots.txt', 5, (bool) preg_match('/^\s*Sitemap\s*:/mi', $robotsText),
        'Checked the Sitemap line.', 'Add "Sitemap: https://your-site/sitemap.xml" to robots.txt.');

    // AI crawlers blocked outright?
    $aiBots = ['gptbot', 'chatgpt-user', 'oai-searchbot', 'claudebot', 'claude-web', 'anthropic-ai', 'perplexitybot', 'google-extended', 'ccbot', 'bytespider', 'applebot-extended', 'amazonbot'];
    $blockedBots = [];
    $agents = [];
    $lastWasAgent = false;
    foreach (preg_split('/\R/', $robotsText) ?: [] as $line) {
        $line = trim(preg_replace('/#.*/', '', $line));
        if ($line === '') {
            continue;
        }
        if (preg_match('/^user-agent\s*:\s*(.+)$/i', $line, $m)) {
            if (!$lastWasAgent) {
                $agents = [];
            }
            $agents[] = strtolower(trim($m[1]));
            $lastWasAgent = true;
            continue;
        }
        $lastWasAgent = false;
        if (preg_match('/^disallow\s*:\s*\/\s*$/i', $line)) {
            foreach ($agents as $a) {
                foreach ($aiBots as $bot) {
                    if ($a === $bot || $a === '*') {
                        $blockedBots[$a === '*' ? '*' : $bot] = true;
                    }
                }
            }
        }
    }
    $add('ai_crawlers_allowed', 'AI crawlers not blocked in robots.txt', 5, !$blockedBots,
        $blockedBots ? 'Blocked: ' . implode(', ', array_keys($blockedBots)) : 'No AI crawler is disallowed from the whole site.',
        'Remove "Disallow: /" for the crawlers you want to reach you.');

    // ---- Sitemap -----------------------------------------------------------
    $sitemapUrl = preg_match('/^\s*Sitemap\s*:\s*(\S+)/mi', $robotsText, $m) ? $m[1] : $origin . '/sitemap.xml';
    $sitemap = $try($sitemapUrl);
    if ($sitemap['status'] !== 200 && $sitemapUrl !== $origin . '/sitemap.xml') {
        $sitemap = $try($origin . '/sitemap.xml');
    }
    $urlCount = 0;
    if ($sitemap['status'] === 200) {
        $urlCount = preg_match_all('#<loc>#i', $sitemap['body']);
    }
    $add('sitemap_valid', 'Sitemap lists pages', 10, $urlCount > 0 ? true : false,
        $urlCount > 0 ? "$urlCount entries." : 'No readable sitemap found.', 'Publish a sitemap.xml that lists every page you want agents to read.');

    // ---- Page content ------------------------------------------------------
    $text = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('#<(script|style)\b.*?</\1>#is', ' ', $html))));
    $textLen = mb_strlen($text);
    $add('content_without_js', 'Content readable without JavaScript', 10, $textLen >= 500 ? true : min(1.0, $textLen / 500) * 0.5,
        "$textLen characters of readable text in the raw HTML.", 'Put the main content in the HTML the server sends, not only in scripts.');

    $hasTitle = (bool) preg_match('#<title[^>]*>\s*\S#i', $html);
    $hasDesc = (bool) preg_match('#<meta[^>]+name=["\']description["\'][^>]+content=["\'][^"\']{20,}#i', $html);
    $hasCanon = (bool) preg_match('#<link[^>]+rel=["\']canonical["\']#i', $html);
    $add('head_metadata', 'Title, description and canonical link', 8, ($hasTitle + $hasDesc + $hasCanon) / 3,
        'Title: ' . ($hasTitle ? 'yes' : 'no') . ', description: ' . ($hasDesc ? 'yes' : 'no') . ', canonical: ' . ($hasCanon ? 'yes' : 'no') . '.',
        'Add all three to the page head.');

    $h1 = preg_match_all('#<h1\b#i', $html);
    $add('single_h1', 'One main heading', 4, $h1 === 1, "$h1 h1 heading(s).", 'Use exactly one h1 per page.');

    // ---- Structured data ---------------------------------------------------
    $types = [];
    if (preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $blocks)) {
        $walk = function ($node) use (&$walk, &$types): void {
            if (!is_array($node)) {
                return;
            }
            if (isset($node['@type'])) {
                foreach ((array) $node['@type'] as $t) {
                    $types[(string) $t] = true;
                }
            }
            foreach ($node as $v) {
                $walk($v);
            }
        };
        foreach ($blocks[1] as $raw) {
            $walk(json_decode(trim($raw), true));
        }
    }
    $businessTypes = ['Organization', 'LocalBusiness', 'ProfessionalService', 'Corporation', 'Store', 'Restaurant', 'MedicalBusiness', 'NGO'];
    $add('json_ld', 'JSON-LD structured data', 6, count($types) > 0, $types ? 'Types: ' . implode(', ', array_keys($types)) : 'None found.', 'Add schema.org JSON-LD describing the site.');
    $add('organization_node', 'Organization or business described', 6, (bool) array_intersect(array_keys($types), $businessTypes),
        'Looked for an Organization or LocalBusiness style type.', 'Add an Organization (or LocalBusiness) node with name, url, address and contact details.');

    // ---- Trust pages -------------------------------------------------------
    $has = fn(string $re) => (bool) preg_match('#href=["\'][^"\']*(' . $re . ')#i', $html);
    $trust = ['about' => $has('about'), 'contact' => $has('contact'), 'terms or privacy' => $has('terms|privacy|legal')];
    $add('trust_pages', 'About, contact and legal pages linked', 6, count(array_filter($trust)) / 3,
        'Linked: ' . (implode(', ', array_keys(array_filter($trust))) ?: 'none') . '.', 'Link an about page, a contact page and a terms or privacy page from the homepage.');

    // ---- llms.txt ----------------------------------------------------------
    $llms = $try($origin . '/llms.txt');
    $llmsText = $llms['status'] === 200 ? $llms['body'] : '';
    $isHtml = (bool) preg_match('#^\s*<(!doctype|html)#i', $llmsText);
    $add('llms_txt', 'llms.txt served', 5, $llmsText !== '' && !$isHtml, $llmsText !== '' && !$isHtml ? 'Found.' : 'Not found.', 'Publish /llms.txt as plain text.');
    $links = preg_match_all('#\[[^\]]+\]\(https?://[^)]+\)#', $llmsText);
    $wellFormed = !$isHtml && preg_match('/^# \S/', $llmsText) && preg_match('/^>\s*\S/m', $llmsText);
    $add('llms_txt_quality', 'llms.txt well formed', 5, ($wellFormed ? 0.4 : 0) + min(1, $links / 3) * 0.6,
        'Title and summary: ' . ($wellFormed ? 'yes' : 'no') . ", Markdown links: $links.", 'Start with "# Name", then "> summary", then sections of Markdown links to key pages.');

    // ---- Markdown negotiation and Link header ------------------------------
    $md = $try($in['url'], ['Accept' => 'text/markdown']);
    $mdType = strtolower($md['headers']['content-type'] ?? '');
    $add('markdown_negotiation', 'Serves Markdown when asked', 5, str_contains($mdType, 'markdown'),
        $md['status'] ? 'Accept: text/markdown returned ' . ($mdType ?: 'no content type') . '.' : 'Could not fetch.',
        'Return a Markdown version when the request says Accept: text/markdown.');
    $link = $home['headers']['link'] ?? '';
    $add('link_header', 'Link response header', 2, (bool) preg_match('#llms\.txt|text/markdown#i', $link),
        $link !== '' ? 'Link: ' . mb_substr($link, 0, 160) : 'No Link header.', 'Send a Link header pointing at llms.txt or the Markdown version.');

    // ---- MCP discovery -----------------------------------------------------
    $card = $try($origin . '/.well-known/mcp/server-card.json');
    $cardJson = $card['status'] === 200 ? json_decode($card['body'], true) : null;
    $cardOk = is_array($cardJson) && !empty($cardJson['name'] ?? $cardJson['serverInfo']['name'] ?? '')
        && !empty($cardJson['endpoint'] ?? $cardJson['transport']['endpoint'] ?? '');
    $add('mcp_server_card', 'MCP server card', 3, $cardOk, $cardOk ? 'Valid card found.' : 'No usable card at /.well-known/mcp/server-card.json.',
        'Publish a server card with a name, version, endpoint and capabilities if you run an MCP server.');

    $score = array_sum(array_column($checks, 'points'));
    $max = array_sum(array_column($checks, 'max'));
    $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 65 ? 'C' : ($score >= 50 ? 'D' : 'F')));
    return [
        'url' => $in['url'],
        'score' => $score,
        'maxScore' => $max,
        'grade' => $grade,
        'failed' => array_values(array_map(fn($c) => $c['label'] . ': ' . ($c['fix'] ?? ''), array_filter($checks, fn($c) => $c['status'] !== 'pass'))),
        'checks' => $checks,
        'note' => 'A practical checklist, not an official standard. Some things (such as scripts registering browser tools) cannot be seen without running a browser.',
    ];
};
