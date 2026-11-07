<?php
declare(strict_types=1);

return function (array $in): array {
    $items = [];
    foreach (Text::lines($in['items']) as $i => $line) {
        $p = array_map('trim', explode('|', $line));
        if (count($p) < 3 || !is_numeric($p[1]) || !is_numeric($p[2])) {
            throw new RuntimeException('Line ' . ($i + 1) . ' should look like: description | quantity | unit price');
        }
        $items[] = ['description' => $p[0], 'quantity' => $p[1] + 0, 'unitPrice' => round((float) $p[2], 2), 'amount' => round($p[1] * $p[2], 2)];
    }
    if (!$items) {
        throw new RuntimeException('Add at least one line item.');
    }
    $sub = array_sum(array_column($items, 'amount'));
    $tax = round($sub * $in['taxPercent'] / 100, 2);
    $c = $in['currency'];
    $date = $in['date'] ?? gmdate('Y-m-d');
    $md = "# {$in['type']} {$in['number']}\n\nDate: $date\n\n";
    if (($in['from'] ?? '') !== '') {
        $md .= "**From:** {$in['from']}\n\n";
    }
    if (($in['to'] ?? '') !== '') {
        $md .= "**Bill to:** {$in['to']}\n\n";
    }
    $md .= "| Description | Qty | Price | Amount |\n| --- | ---: | ---: | ---: |\n";
    foreach ($items as $it) {
        $md .= '| ' . str_replace('|', '/', $it['description']) . " | {$it['quantity']} | " . Text::money($it['unitPrice'], $c) . ' | ' . Text::money($it['amount'], $c) . " |\n";
    }
    $md .= "\nSubtotal: " . Text::money($sub, $c) . "  \nTax ({$in['taxPercent']}%): " . Text::money($tax, $c) . "  \n**Total: " . Text::money($sub + $tax, $c) . "**\n";
    if (($in['notes'] ?? '') !== '') {
        $md .= "\n{$in['notes']}\n";
    }
    return ['items' => $items, 'subtotal' => round($sub, 2), 'tax' => $tax, 'total' => round($sub + $tax, 2), 'markdown' => $md];
};
