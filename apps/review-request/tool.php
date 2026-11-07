<?php
declare(strict_types=1);

return function (array $in): array {
    $name = ($in['customer'] ?? '') !== '' ? $in['customer'] : 'there';
    $biz = $in['business'];
    $sig = ($in['from'] ?? '') !== '' ? "\n" . $in['from'] : '';
    $job = preg_replace('/^to /i', '', $in['job'] ?? '');
    $thanks = $job !== '' ? "Thanks again for choosing $biz to $job." : "Thanks again for choosing $biz.";
    $link = $in['link'];
    $out = [
        ['label' => 'Short and warm', 'text' => "Hi $name, $thanks If you were happy with it, would you leave us a quick review? It really helps a small business like ours.\n$link$sig"],
        ['label' => 'Very short', 'text' => "Hi $name, it was great working with you. Would you mind sharing a quick review? $link$sig"],
        ['label' => 'Asks for honest feedback', 'text' => "Hi $name, $thanks We'd love to hear how it went, good or bad. If you have a minute, you can leave a review here: $link\nAnd if anything wasn't right, just reply and we'll sort it out.$sig"],
    ];
    foreach ($out as &$m) {
        if ($in['channel'] === 'SMS') {
            $m['text'] = str_replace("\n", ' ', $m['text']);
        } elseif ($in['channel'] === 'Email') {
            $m['subject'] = "A quick favour from $biz";
            $m['text'] = preg_replace('/^Hi ([^,]+), /', "Hi $1,\n\n", $m['text']);
        }
    }
    return ['messages' => $out, 'note' => 'Ask every happy customer the same way. Offering rewards for reviews breaks Google\'s rules.'];
};
