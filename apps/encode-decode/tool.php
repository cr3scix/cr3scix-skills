<?php
declare(strict_types=1);

return function (array $in): array {
    $t = $in['text'];
    $b64d = function (string $s): string {
        $s = strtr(trim($s), '-_', '+/');
        $s .= str_repeat('=', (4 - strlen($s) % 4) % 4);
        $raw = base64_decode($s, true);
        if ($raw === false || !mb_check_encoding($raw, 'UTF-8')) {
            throw new RuntimeException('That is not valid Base64 text.');
        }
        return $raw;
    };
    switch ($in['operation']) {
        case 'base64-encode': return ['result' => base64_encode($t)];
        case 'base64-decode': return ['result' => $b64d($t)];
        case 'url-encode': return ['result' => rawurlencode($t)];
        case 'url-decode': return ['result' => rawurldecode($t)];
        case 'html-escape': return ['result' => htmlspecialchars($t, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8')];
        case 'html-unescape': return ['result' => html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
        case 'jwt-decode':
            $parts = explode('.', trim($t));
            if (count($parts) < 2) {
                throw new RuntimeException('A JWT has three parts separated by dots.');
            }
            $h = json_decode($b64d($parts[0]), true);
            $p = json_decode($b64d($parts[1]), true);
            if (!is_array($h) || !is_array($p)) {
                throw new RuntimeException('The header or payload is not JSON.');
            }
            $times = [];
            foreach (['iat', 'nbf', 'exp'] as $k) {
                if (isset($p[$k]) && is_numeric($p[$k])) {
                    $times[$k] = gmdate('c', (int) $p[$k]);
                }
            }
            return ['header' => $h, 'payload' => $p, 'times' => $times, 'expired' => isset($p['exp']) && is_numeric($p['exp']) && $p['exp'] < time(), 'note' => 'The signature is not checked. Never trust a token because it decodes.'];
    }
    throw new RuntimeException('Unknown operation.');
};
