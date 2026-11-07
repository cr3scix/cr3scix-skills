<?php
declare(strict_types=1);

return function (array $in): array {
    try {
        $data = json_decode($in['json'], false, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
    } catch (JsonException $e) {
        return ['valid' => false, 'error' => $e->getMessage()];
    }
    if ($in['action'] === 'validate') {
        return ['valid' => true];
    }
    $sort = function ($v) use (&$sort) {
        if (is_array($v)) {
            return array_map($sort, $v);
        }
        if ($v instanceof stdClass) {
            $vars = get_object_vars($v);
            ksort($vars, SORT_STRING);
            $o = new stdClass();
            foreach ($vars as $k => $x) {
                $o->{$k} = $sort($x);
            }
            return $o;
        }
        return $v;
    };
    if ($in['action'] === 'sort') {
        $data = $sort($data);
    }
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;
    if ($in['action'] === 'minify') {
        return ['valid' => true, 'result' => json_encode($data, $flags)];
    }
    $out = (string) json_encode($data, $flags | JSON_PRETTY_PRINT);
    $n = $in['indent'];
    $out = preg_replace_callback('/^( +)/m', fn($m) => str_repeat(' ', intdiv(strlen($m[1]), 4) * $n), $out) ?? $out;
    return ['valid' => true, 'result' => $out];
};
