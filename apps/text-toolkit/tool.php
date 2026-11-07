<?php
declare(strict_types=1);

return function (array $in): array {
    $t = $in['text'];
    $words = preg_match_all('/\S+/u', $t);
    $sentences = preg_match_all('/[^.!?]+[.!?]+(\s|$)/u', $t) ?: ($words ? 1 : 0);
    $paras = count(array_filter(preg_split('/\R\s*\R/u', $t) ?: [], fn($p) => trim($p) !== ''));
    $small = '/^(a|an|and|as|at|but|by|for|in|nor|of|on|or|the|to|up|via)$/i';
    $result = match ($in['transform']) {
        'upper' => mb_strtoupper($t),
        'lower' => mb_strtolower($t),
        'title' => (function () use ($t, $small) {
            $i = 0;
            return preg_replace_callback("/[\\p{L}\\p{N}'’]+/u", function ($m) use (&$i, $small) {
                $w = mb_strtolower($m[0]);
                return ($i++ && preg_match($small, $w)) ? $w : mb_strtoupper(mb_substr($w, 0, 1)) . mb_substr($w, 1);
            }, $t);
        })(),
        'sentence' => preg_replace_callback('/(^\s*|[.!?]\s+)(\p{Ll})/u', fn($m) => $m[1] . mb_strtoupper($m[2]), mb_strtolower($t)),
        'slug' => Text::slug($t),
        'tidy' => trim(preg_replace(['/[ \t]+/', '/ *\R */', '/\n{3,}/'], [' ', "\n", "\n\n"], $t) ?? $t),
        'dedupe-lines' => implode("\n", array_values(array_unique(preg_split('/\R/u', $t) ?: []))),
        'sort-lines' => (function () use ($t) { $l = Text::lines($t); natcasesort($l); return implode("\n", $l); })(),
        default => null,
    };
    $out = [
        'words' => $words,
        'characters' => mb_strlen($t),
        'charactersWithoutSpaces' => mb_strlen(preg_replace('/\s/u', '', $t) ?? ''),
        'sentences' => $sentences,
        'paragraphs' => $paras,
        'readingMinutes' => $words ? max(1, (int) round($words / 200)) : 0,
        'speakingMinutes' => $words ? max(1, (int) round($words / 130)) : 0,
    ];
    if ($result !== null) {
        $out['result'] = $result;
    }
    return $out;
};
