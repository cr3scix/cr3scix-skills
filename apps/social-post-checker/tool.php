<?php
declare(strict_types=1);

return function (array $in): array {
    // Same data file the browser app uses, so both always agree.
    $js = (string) file_get_contents(__DIR__ . '/vendor/platforms.js');
    preg_match_all('/window\.(CS_\w+)\s*=\s*(.+?);\s*(?=window\.|$)/s', $js, $m, PREG_SET_ORDER);
    $data = [];
    foreach ($m as $set) {
        $data[$set[1]] = json_decode($set[2], true);
    }
    $P = $data['CS_PLATFORMS'] ?? [];
    $keys = $in['platforms'] === 'all' ? array_keys($P) : array_map('trim', explode(',', strtolower($in['platforms'])));
    $t = $in['text'];
    $low = mb_strtolower($t);
    $urlRe = '~https?://\S+|www\.\S+~i';
    $out = [];
    foreach ($keys as $k) {
        if (!isset($P[$k])) {
            throw new RuntimeException("Unknown platform: $k. Use one of " . implode(', ', array_keys($P)) . '.');
        }
        $p = $P[$k];
        if (str_starts_with($k, 'x')) {
            $len = 23 * preg_match_all($urlRe, $t);
            foreach (mb_str_split((string) preg_replace($urlRe, '', $t)) as $ch) {
                $c = mb_ord($ch);
                $len += ($c <= 4351 || ($c >= 8192 && $c <= 8205) || ($c >= 8208 && $c <= 8223) || ($c >= 8242 && $c <= 8247)) ? 1 : 2;
            }
        } else {
            $len = mb_strlen(class_exists('Normalizer') ? (string) Normalizer::normalize($t) : $t);
        }
        $r = ['platform' => $p['name'], 'length' => $len, 'limit' => $p['limit'], 'errors' => [], 'warnings' => []];
        if ($len > $p['limit']) {
            $r['errors'][] = ($len - $p['limit']) . " characters over the {$p['limit']} limit";
        }
        $tags = preg_match_all('/(?<!\w)#[\w\x{00c0}-\x{ffff}]+/u', $t, $tm) ? $tm[0] : [];
        if ($p['hashtags'] !== null && count($tags) > $p['hashtags']) {
            $r['warnings'][] = count($tags) . " hashtags; {$p['hashtags']} or fewer is safer here";
        }
        if (preg_match($urlRe, $t) && !$p['link_ok']) {
            $r['warnings'][] = in_array($k, ['instagram', 'tiktok'], true) ? 'Links in captions are not clickable here' : 'A link in the body tends to cut reach; try the first comment or reply';
        }
        $first = explode("\n", trim($t))[0];
        if (mb_strlen($first) > $p['preview']) {
            $r['warnings'][] = 'First line is ' . mb_strlen($first) . " characters; about {$p['preview']} show before \"more\"";
        }
        if (preg_match('/[\x{2013}\x{2014}]/u', $t)) {
            $r['warnings'][] = 'Contains an em or en dash, a common sign of AI-written text';
        }
        $slop = array_values(array_filter($data['CS_SLOP'] ?? [], fn($w) => str_contains($low, $w)));
        if ($slop) {
            $r['warnings'][] = 'Stock phrases: ' . implode(', ', $slop);
        }
        $bait = array_values(array_filter($data['CS_BAIT'] ?? [], fn($w) => str_contains($low, $w)));
        if ($bait) {
            $r['warnings'][] = 'Engagement bait some platforms demote: ' . implode(', ', $bait);
        }
        if (preg_match('/\{\{[^}]+\}\}|\[(your|insert)[^\]]*\]/i', $t)) {
            $r['errors'][] = 'Unfilled placeholder left in the draft';
        }
        $out[] = $r;
    }
    return ['results' => $out, 'readyEverywhere' => !array_filter($out, fn($r) => $r['errors'])];
};
