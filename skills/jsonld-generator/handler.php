<?php
declare(strict_types=1);

return function (array $in): array {
    $node = [
        '@context' => 'https://schema.org',
        '@type' => $in['type'],
        'name' => $in['name'],
        'url' => $in['url'],
    ];
    foreach (['description', 'email', 'telephone'] as $key) {
        if (!empty($in[$key])) {
            $node[$key] = $in[$key];
        }
    }
    if (!empty($in['logo'])) {
        $node['logo'] = ['@type' => 'ImageObject', 'url' => $in['logo']];
    }
    $address = array_filter([
        'streetAddress' => $in['streetAddress'] ?? null,
        'addressLocality' => $in['city'] ?? null,
        'addressRegion' => $in['region'] ?? null,
        'postalCode' => $in['postalCode'] ?? null,
        'addressCountry' => isset($in['country']) ? strtoupper($in['country']) : null,
    ]);
    if ($address) {
        $node['address'] = ['@type' => 'PostalAddress'] + $address;
    }
    $links = [];
    foreach (preg_split('/\R/', $in['sameAs'] ?? '') ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (!filter_var($line, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $line)) {
            throw new RuntimeException("Profile link is not a valid URL: $line");
        }
        $links[] = $line;
    }
    if ($links) {
        $node['sameAs'] = $links;
    }

    $json = json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $missing = [];
    foreach (['description' => 'description', 'email' => 'email or telephone', 'address' => 'address', 'logo' => 'logo', 'sameAs' => 'profile links'] as $key => $label) {
        if (!isset($node[$key]) && !($key === 'email' && isset($node['telephone']))) {
            $missing[] = $label;
        }
    }
    return [
        'jsonLd' => $node,
        'scriptTag' => '<script type="application/ld+json">' . str_replace('</', '<\/', $json) . '</script>',
        'suggestions' => $missing ? ['Adding these would make the data more complete: ' . implode(', ', $missing) . '.'] : [],
    ];
};
