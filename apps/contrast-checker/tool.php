<?php
declare(strict_types=1);

return function (array $in): array {
    $hex = function (string $h): string {
        $h = ltrim(trim($h), '#');
        if (strlen($h) === 3) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        if (!preg_match('/^[0-9a-f]{6}$/i', $h)) {
            throw new RuntimeException('Colors must be hex, like #1a1917 or #fff.');
        }
        return strtolower($h);
    };
    $lum = function (string $h): float {
        $s = 0.0;
        foreach ([0.2126, 0.7152, 0.0722] as $i => $w) {
            $c = hexdec(substr($h, $i * 2, 2)) / 255;
            $s += $w * ($c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4);
        }
        return $s;
    };
    $a = $lum($hex($in['foreground']));
    $b = $lum($hex($in['background']));
    $r = (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    return ['ratio' => floor($r * 100) / 100, 'aaNormal' => $r >= 4.5, 'aaLarge' => $r >= 3, 'aaaNormal' => $r >= 7, 'aaaLarge' => $r >= 4.5, 'nonText' => $r >= 3];
};
