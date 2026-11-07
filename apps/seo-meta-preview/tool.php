<?php
declare(strict_types=1);

return function (array $in): array {
    $verdict = function (int $len, int $lo, int $hi): string {
        return $len < $lo ? "short (aim for $lo to $hi)" : ($len <= $hi ? 'good' : 'too long, likely cut off');
    };
    $t = Text::attr($in['title']);
    $d = Text::attr($in['description']);
    $tags = "<title>$t</title>\n<meta name=\"description\" content=\"$d\">\n";
    if (!empty($in['url'])) {
        $tags .= '<link rel="canonical" href="' . Text::attr($in['url']) . "\">\n";
    }
    $tags .= "<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:title\" content=\"$t\">\n<meta property=\"og:description\" content=\"$d\">\n";
    if (!empty($in['url'])) {
        $tags .= '<meta property="og:url" content="' . Text::attr($in['url']) . "\">\n";
    }
    if (!empty($in['imageUrl'])) {
        $tags .= '<meta property="og:image" content="' . Text::attr($in['imageUrl']) . "\">\n";
    }
    $tags .= '<meta name="twitter:card" content="' . (empty($in['imageUrl']) ? 'summary' : 'summary_large_image') . '">';
    $tl = mb_strlen($in['title']);
    $dl = mb_strlen($in['description']);
    return ['titleLength' => $tl, 'title' => $verdict($tl, 30, 60), 'descriptionLength' => $dl, 'description' => $verdict($dl, 70, 160), 'tags' => $tags];
};
