<?php
declare(strict_types=1);

return function (array $in): array {
    $cost = (float) $in['unitCost'];
    $price = (float) $in['price'];
    $profit = $price - $cost;
    $fixed = (float) $in['fixedCosts'];
    $breakEven = ($profit > 0 && $fixed > 0) ? (int) ceil($fixed / $profit) : null;
    return [
        'profitPerUnit' => round($profit, 2),
        'marginPercent' => $price > 0 ? round($profit / $price * 100, 1) : null,
        'markupPercent' => $cost > 0 ? round($profit / $cost * 100, 1) : null,
        'priceForTargetMargin' => round($cost / (1 - $in['targetMarginPercent'] / 100), 2),
        'breakEvenUnits' => $breakEven,
        'breakEvenRevenue' => $breakEven !== null ? round($breakEven * $price, 2) : null,
        'note' => $fixed > 0 && $profit <= 0 ? 'Break-even is not reachable: each sale loses money.' : 'Margin is profit as a share of price; markup is profit as a share of cost.',
    ];
};
