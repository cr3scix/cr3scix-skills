<?php
declare(strict_types=1);

return function (array $in): array {
    try {
        $tz = new DateTimeZone($in['zone']);
    } catch (Exception $e) {
        throw new RuntimeException('Unknown time zone. Use names like Asia/Kolkata.');
    }
    $v = trim($in['value']);
    if (preg_match('/^-?\d+(\.\d+)?$/', $v)) {
        $n = (float) $v;
        $ms = abs($n) >= 1e11 ? $n : $n * 1000;
        $d = (new DateTimeImmutable('@' . sprintf('%.3F', $ms / 1000)))->setTimezone($tz);
    } else {
        try {
            $d = (new DateTimeImmutable($v, $tz))->setTimezone($tz);
        } catch (Exception $e) {
            throw new RuntimeException('Could not read that date. Try 2026-03-01 14:30 or a Unix timestamp.');
        }
    }
    return ['unixSeconds' => $d->getTimestamp(), 'unixMilliseconds' => (int) $d->format('Uv'), 'iso8601Utc' => $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'), 'local' => $d->format('l, j F Y H:i:s T'), 'zone' => $in['zone']];
};
