<?php
declare(strict_types=1);

return function (array $in): array {
    $weeks = $in['workingWeeks'];
    $hoursYear = $in['teamSize'] * $in['hoursPerWeekEach'] * $weeks;
    $costYear = $hoursYear * $in['hourlyCost'];
    $share = $in['automatablePercent'] / 100;
    $hoursSaved = $hoursYear * $share;
    $costSaved = $hoursSaved * $in['hourlyCost'];

    $result = [
        'manualHoursPerYear' => round($hoursYear, 1),
        'manualCostPerYear' => round($costYear, 2),
        'automatableHoursPerYear' => round($hoursSaved, 1),
        'estimatedSavingPerYear' => round($costSaved, 2),
        'hoursFreedPerPersonPerWeek' => $in['teamSize'] > 0 ? round($hoursSaved / $weeks / $in['teamSize'], 2) : 0,
        'note' => 'An estimate from the numbers you entered, not a quote. Time spent checking that the automation ran correctly is not subtracted.',
    ];
    if (isset($in['buildCost'])) {
        $result['paybackMonths'] = $costSaved > 0 ? round($in['buildCost'] / ($costSaved / 12), 1) : null;
    }
    return $result;
};
