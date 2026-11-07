<?php
declare(strict_types=1);

return function (array $in): array {
    if ($in['direction'] === 'json-to-csv') {
        $data = json_decode($in['input'], true);
        if (!is_array($data) || !array_is_list($data)) {
            throw new RuntimeException('Use a JSON array of objects, like [{"a":1}].');
        }
        $cols = [];
        foreach ($data as $row) {
            if (!is_array($row) || array_is_list($row) && $row !== []) {
                throw new RuntimeException('Every item must be an object.');
            }
            foreach (array_keys($row) as $k) {
                $cols[$k] = true;
            }
        }
        $cols = array_keys($cols);
        $h = fopen('php://memory', 'w+');
        $cell = function ($v): string {
            $v = $v === null ? '' : (is_scalar($v) ? (is_bool($v) ? ($v ? 'true' : 'false') : (string) $v) : (string) json_encode($v));
            return preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v; // keep spreadsheet formulas from running
        };
        fputcsv($h, array_map('strval', $cols), ',', '"', '');
        foreach ($data as $row) {
            fputcsv($h, array_map(fn($c) => $cell($row[$c] ?? null), $cols), ',', '"', '');
        }
        rewind($h);
        return ['csv' => rtrim((string) stream_get_contents($h), "\n"), 'rows' => count($data), 'columns' => count($cols)];
    }
    $first = strtok($in['input'], "\n") ?: '';
    $counts = [',' => substr_count($first, ','), ';' => substr_count($first, ';'), "\t" => substr_count($first, "\t"), '|' => substr_count($first, '|')];
    arsort($counts);
    $delim = (string) array_key_first($counts);
    $h = fopen('php://memory', 'w+');
    fwrite($h, $in['input']);
    rewind($h);
    $head = fgetcsv($h, null, $delim, '"', '');
    if (!$head || $head === [null]) {
        throw new RuntimeException('Paste some CSV with a header row.');
    }
    $rows = [];
    while (($r = fgetcsv($h, null, $delim, '"', '')) !== false) {
        if ($r === [null]) {
            continue;
        }
        $o = [];
        foreach ($head as $i => $k) {
            $v = $r[$i] ?? '';
            $o[$k] = $in['convertNumbers'] && $v !== '' && is_numeric($v) && !preg_match('/^0\d/', $v) ? $v + 0 : $v;
        }
        $rows[] = $o;
    }
    return ['json' => $rows, 'rows' => count($rows), 'columns' => count($head), 'delimiter' => $delim === "\t" ? 'tab' : $delim];
};
