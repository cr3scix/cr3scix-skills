<?php
declare(strict_types=1);

return function (array $in): array {
    $rate = (float) $in['ratePercent'];
    $base = $in['mode'] === 'add' ? (float) $in['amount'] : $in['amount'] / (1 + $rate / 100);
    $gst = $base * $rate / 100;
    $out = ['amountBeforeGst' => round($base, 2), 'gst' => round($gst, 2), 'total' => round($base + $gst, 2)];
    if ($in['supply'] === 'intra') {
        $out['cgst'] = round($gst / 2, 2);
        $out['sgst'] = round($gst / 2, 2);
    } else {
        $out['igst'] = round($gst, 2);
    }
    $out['note'] = 'Main slabs since 22 September 2025: 5%, 18% and 40%, with 0%, 0.25% and 3% for specific goods. A calculator, not tax advice.';
    return $out;
};
