<?php
declare(strict_types=1);

return function (array $in): array {
    $p = ['You are ' . (($in['role'] ?? '') !== '' ? $in['role'] : 'a helpful assistant') . '.'];
    $p[] = "<task>\n{$in['task']}\n</task>";
    if (($in['context'] ?? '') !== '') {
        $p[] = "<context>\n{$in['context']}\n</context>";
    }
    if (($in['input'] ?? '') !== '') {
        $p[] = "<input>\nI will give you: {$in['input']}\n</input>";
    }
    $steps = Text::lines($in['steps'] ?? '');
    if ($steps) {
        $p[] = "<steps>\n" . implode("\n", array_map(fn($s, $i) => ($i + 1) . ". $s", $steps, array_keys($steps))) . "\n</steps>";
    }
    $rules = Text::lines($in['rules'] ?? '');
    if ($rules) {
        $p[] = "<rules>\n" . implode("\n", array_map(fn($r) => "- $r", $rules)) . "\n</rules>";
    }
    if (($in['example'] ?? '') !== '') {
        $p[] = "<example>\n{$in['example']}\n</example>";
    }
    $p[] = "<output>\nFormat: {$in['format']}.\nTone: {$in['tone']}.\nIf something you need is missing, ask me before you answer.\n</output>";
    return ['prompt' => implode("\n\n", $p)];
};
