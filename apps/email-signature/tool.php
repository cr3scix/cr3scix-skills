<?php
declare(strict_types=1);

return function (array $in): array {
    $e = fn($s) => Text::attr((string) $s);
    $c = preg_match('/^#[0-9a-f]{6}$/i', $in['color']) ? $in['color'] : '#0f766e';
    $web = trim($in['website'] ?? '');
    $href = $web !== '' && !preg_match('#^https?://#i', $web) ? 'https://' . $web : $web;
    $bits = [];
    if (($in['phone'] ?? '') !== '') {
        $bits[] = '<a href="tel:' . $e(preg_replace('/[^\d+]/', '', $in['phone'])) . '" style="color:#333;text-decoration:none">' . $e($in['phone']) . '</a>';
    }
    if (($in['email'] ?? '') !== '') {
        $bits[] = '<a href="mailto:' . $e($in['email']) . '" style="color:#333;text-decoration:none">' . $e($in['email']) . '</a>';
    }
    if ($web !== '') {
        $bits[] = '<a href="' . $e($href) . '" style="color:' . $c . ';text-decoration:none">' . $e(preg_replace('#^https?://#i', '', $web)) . '</a>';
    }
    $logo = str_starts_with($in['logoUrl'] ?? '', 'https://') ? '<td style="padding-right:14px;vertical-align:top"><img src="' . $e($in['logoUrl']) . '" alt="' . $e($in['company'] ?? '') . '" width="64" style="display:block;border:0;max-width:64px"></td>' : '';
    $line2 = implode(', ', array_filter([$in['role'] ?? '', $in['company'] ?? '']));
    $html = '<table cellpadding="0" cellspacing="0" border="0" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.5;color:#333"><tr>' . $logo
        . '<td style="border-left:3px solid ' . $c . ';padding-left:12px;vertical-align:top"><div style="font-size:15px;font-weight:bold;color:#111">' . $e($in['name']) . '</div>'
        . '<div>' . $e($line2) . '</div><div>' . implode(' &nbsp;|&nbsp; ', $bits) . '</div></td></tr></table>';
    return ['html' => $html];
};
