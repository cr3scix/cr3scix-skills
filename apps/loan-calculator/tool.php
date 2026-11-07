<?php
declare(strict_types=1);

return function (array $in): array {
    $p = (float) $in['principal'];
    $r = $in['annualRatePercent'] / 1200;
    $n = $in['months'];
    $emi = $r > 0 ? $p * $r * (1 + $r) ** $n / ((1 + $r) ** $n - 1) : $p / $n;
    $bal = $p;
    $years = [];
    for ($m = 1; $m <= $n; $m++) {
        $int = $bal * $r;
        $bal = max(0.0, $bal - ($emi - $int));
        $y = (int) ceil($m / 12);
        $years[$y] ??= ['year' => $y, 'principalPaid' => 0.0, 'interestPaid' => 0.0, 'balance' => 0.0];
        $years[$y]['principalPaid'] += $emi - $int;
        $years[$y]['interestPaid'] += $int;
        $years[$y]['balance'] = $bal;
    }
    $years = array_map(fn($y) => array_map(fn($v) => is_float($v) ? round($v, 2) : $v, $y), array_values($years));
    return ['emi' => round($emi, 2), 'totalInterest' => round($emi * $n - $p, 2), 'totalPaid' => round($emi * $n, 2), 'byYear' => $years];
};
