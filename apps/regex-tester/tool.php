<?php
declare(strict_types=1);

return function (array $in): array {
    $mods = count_chars(preg_replace('/[^imsux]/', '', $in['flags'] ?? '') ?? '', 3);
    $re = "\x01" . $in['pattern'] . "\x01" . $mods . (str_contains($mods, 'u') ? '' : 'u');
    ini_set('pcre.backtrack_limit', '200000');
    set_error_handler(fn() => true);
    $n = preg_match_all($re, $in['text'], $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    restore_error_handler();
    if ($n === false) {
        $err = preg_last_error() === PREG_INTERNAL_ERROR ? 'not a valid pattern' : preg_last_error_msg();
        throw new RuntimeException('Regex failed: ' . $err . '.');
    }
    $out = [];
    foreach (array_slice($m, 0, 500) as $set) {
        $row = ['match' => $set[0][0], 'position' => mb_strlen(substr($in['text'], 0, $set[0][1]))];
        foreach ($set as $k => $g) {
            if ($k !== 0) {
                $row['groups'][(string) $k] = $g[0];
            }
        }
        $out[] = $row;
    }
    return ['count' => $n, 'matches' => $out, 'note' => 'Uses PCRE, which matches JavaScript regex for almost all common patterns.'];
};
