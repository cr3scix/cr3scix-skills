<?php
declare(strict_types=1);

return function (array $in): array {
    $bots = [
        ['OAI-SearchBot', 'search'], ['ChatGPT-User', 'user'], ['GPTBot', 'training'],
        ['Claude-SearchBot', 'search'], ['Claude-User', 'user'], ['ClaudeBot', 'training'],
        ['PerplexityBot', 'search'], ['Perplexity-User', 'user'],
        ['Google-Extended', 'training'], ['Applebot-Extended', 'training'], ['Meta-ExternalAgent', 'training'],
        ['CCBot', 'training'], ['Bytespider', 'training'], ['Amazonbot', 'training'],
    ];
    $blocked = [];
    foreach ($bots as [$name, $kind]) {
        if ($in['policy'] === 'block-all-ai' || ($in['policy'] === 'allow-search-block-training' && $kind === 'training')) {
            $blocked[] = $name;
        }
    }
    $t = "# Generated with Cr3scix Toolkit\n";
    foreach ($blocked as $b) {
        $t .= "\nUser-agent: $b\nDisallow: /\n";
    }
    $t .= "\nUser-agent: *\nContent-Signal: search={$in['search']}, ai-input={$in['aiInput']}, ai-train={$in['aiTrain']}\nAllow: /\n";
    foreach (Text::lines($in['disallowPaths'] ?? '') as $p) {
        $t .= 'Disallow: ' . ($p[0] === '/' ? $p : '/' . $p) . "\n";
    }
    if (!empty($in['sitemap'])) {
        $t .= "\nSitemap: {$in['sitemap']}\n";
    }
    return ['robotsTxt' => $t, 'blocked' => $blocked, 'note' => 'robots.txt is a request crawlers may ignore. Crawler names change, so review this list now and then.'];
};
