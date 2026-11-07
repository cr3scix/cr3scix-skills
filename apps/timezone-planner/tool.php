<?php
declare(strict_types=1);

return function (array $in): array {
    $zone = function (string $z): DateTimeZone {
        try {
            return new DateTimeZone(trim($z));
        } catch (Exception $e) {
            throw new RuntimeException("Unknown time zone: $z. Use names like Europe/London.");
        }
    };
    $base = $zone($in['baseZone']);
    $when = DateTimeImmutable::createFromFormat('Y-m-d H:i', $in['dateTime'], $base);
    if (!$when) {
        throw new RuntimeException('Use the format YYYY-MM-DD HH:MM for the date and time.');
    }
    $rows = [];
    foreach (array_merge([$in['baseZone']], preg_split('/[\s,]+/', $in['zones']) ?: []) as $z) {
        if (trim($z) === '') {
            continue;
        }
        $local = $when->setTimezone($zone($z));
        $h = (int) $local->format('G');
        $rows[] = ['zone' => trim($z), 'localTime' => $local->format('D j M Y, H:i'), 'utcOffset' => $local->format('P'), 'fitsWorkingDay' => $h >= 9 && $h < 18 ? 'yes' : ($h >= 7 && $h < 21 ? 'early or late' : 'no')];
    }
    return ['utc' => $when->setTimezone(new DateTimeZone('UTC'))->format('c'), 'times' => $rows];
};
