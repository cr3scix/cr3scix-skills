<?php
declare(strict_types=1);

/** Small text helpers shared by tool twins. */
final class Text
{
    public static function lines(string $s): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $s) ?: []), fn($l) => $l !== ''));
    }

    public static function slug(string $s): string
    {
        $s = class_exists('Normalizer') ? (string) Normalizer::normalize($s, Normalizer::FORM_D) : $s;
        $s = preg_replace('/\p{Mn}+/u', '', $s) ?? $s;
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)) ?? '', '-');
    }

    public static function attr(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function money(float $n, string $cur = ''): string
    {
        return $cur . number_format($n, 2);
    }
}
