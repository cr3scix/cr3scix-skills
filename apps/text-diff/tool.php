<?php
declare(strict_types=1);

return function (array $in): array {
    $a = explode("\n", str_replace("\r\n", "\n", $in['original']));
    $b = explode("\n", str_replace("\r\n", "\n", $in['changed']));
    $n = count($a);
    $m = count($b);
    if ($n * $m > 4000000) {
        throw new RuntimeException('Texts are too long to compare (about 2,000 lines each is the limit).');
    }
    $L = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
    for ($i = $n - 1; $i >= 0; $i--) {
        for ($j = $m - 1; $j >= 0; $j--) {
            $L[$i][$j] = $a[$i] === $b[$j] ? $L[$i + 1][$j + 1] + 1 : max($L[$i + 1][$j], $L[$i][$j + 1]);
        }
    }
    $out = [];
    $i = $j = 0;
    while ($i < $n && $j < $m) {
        if ($a[$i] === $b[$j]) {
            $out[] = '  ' . $a[$i++];
            $j++;
        } elseif ($L[$i + 1][$j] >= $L[$i][$j + 1]) {
            $out[] = '- ' . $a[$i++];
        } else {
            $out[] = '+ ' . $b[$j++];
        }
    }
    while ($i < $n) {
        $out[] = '- ' . $a[$i++];
    }
    while ($j < $m) {
        $out[] = '+ ' . $b[$j++];
    }
    $added = count(array_filter($out, fn($l) => $l[0] === '+'));
    $removed = count(array_filter($out, fn($l) => $l[0] === '-'));
    return ['added' => $added, 'removed' => $removed, 'same' => $added + $removed === 0, 'diff' => implode("\n", $out)];
};
