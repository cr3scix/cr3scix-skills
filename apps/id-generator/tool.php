<?php
declare(strict_types=1);

return function (array $in): array {
    $pick = fn(string $s): string => $s[random_int(0, strlen($s) - 1)];
    $out = [];
    for ($i = 0; $i < $in['count']; $i++) {
        if ($in['type'] === 'uuid') {
            $b = random_bytes(16);
            $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
            $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
            $out[] = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
        } elseif ($in['type'] === 'hex') {
            $out[] = bin2hex(random_bytes(max(1, intdiv($in['length'] + 1, 2))));
        } else {
            $sets = array_filter([
                $in['lowercase'] ? 'abcdefghijkmnopqrstuvwxyz' : '',
                $in['uppercase'] ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : '',
                $in['digits'] ? '23456789' : '',
                $in['symbols'] ? '!@#$%^&*()-_=+[]{}?' : '',
            ]);
            if (!$sets) {
                throw new RuntimeException('Turn on at least one character type.');
            }
            $chars = array_map($pick, array_values($sets));
            $all = implode('', $sets);
            while (count($chars) < $in['length']) {
                $chars[] = $pick($all);
            }
            for ($j = count($chars) - 1; $j > 0; $j--) {
                $k = random_int(0, $j);
                [$chars[$j], $chars[$k]] = [$chars[$k], $chars[$j]];
            }
            $out[] = implode('', $chars);
        }
    }
    return ['values' => $out, 'note' => 'Generated with a cryptographically secure random source.'];
};
