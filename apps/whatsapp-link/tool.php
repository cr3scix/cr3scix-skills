<?php
declare(strict_types=1);

return function (array $in): array {
    $d = preg_replace('/\D/', '', $in['phone']) ?? '';
    if (strlen($d) < 8 || strlen($d) > 15) {
        throw new RuntimeException('Use the full international number with country code (8 to 15 digits).');
    }
    $msg = trim($in['message'] ?? '');
    return ['url' => 'https://wa.me/' . $d . ($msg !== '' ? '?text=' . rawurlencode($msg) : '')];
};
