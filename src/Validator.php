<?php
declare(strict_types=1);

/**
 * Validates input against the small JSON Schema subset used by skill.json:
 * type (string, integer, number, boolean), required, enum, default,
 * minLength, maxLength, minimum, maximum and format (url, email).
 * Unknown properties are dropped.
 */
final class Validator
{
    /** @return array{0: array<string,mixed>, 1: list<string>} cleaned input and error messages */
    public static function check(array $schema, array $input): array
    {
        $props = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];
        $clean = [];
        $errors = [];

        foreach ($props as $name => $rule) {
            $present = array_key_exists($name, $input) && $input[$name] !== '' && $input[$name] !== null;
            if (!$present) {
                if (array_key_exists('default', $rule)) {
                    $clean[$name] = $rule['default'];
                } elseif (in_array($name, $required, true)) {
                    $errors[] = "$name is required";
                }
                continue;
            }
            [$value, $error] = self::coerce($name, $rule, $input[$name]);
            if ($error !== null) {
                $errors[] = $error;
            } else {
                $clean[$name] = $value;
            }
        }
        return [$clean, $errors];
    }

    /** @return array{0: mixed, 1: ?string} */
    private static function coerce(string $name, array $rule, mixed $value): array
    {
        $type = $rule['type'] ?? 'string';
        switch ($type) {
            case 'boolean':
                if (is_bool($value)) {
                    return [$value, null];
                }
                if (in_array($value, ['true', '1', 1, 'on', 'yes'], true)) {
                    return [true, null];
                }
                if (in_array($value, ['false', '0', 0, 'off', 'no'], true)) {
                    return [false, null];
                }
                return [null, "$name must be true or false"];

            case 'integer':
            case 'number':
                if (is_string($value) && is_numeric($value)) {
                    $value = $value + 0;
                }
                if (!is_int($value) && !is_float($value)) {
                    return [null, "$name must be a number"];
                }
                if ($type === 'integer' && floor((float) $value) != $value) {
                    return [null, "$name must be a whole number"];
                }
                if ($type === 'integer') {
                    $value = (int) $value;
                }
                if (isset($rule['minimum']) && $value < $rule['minimum']) {
                    return [null, "$name must be at least {$rule['minimum']}"];
                }
                if (isset($rule['maximum']) && $value > $rule['maximum']) {
                    return [null, "$name must be at most {$rule['maximum']}"];
                }
                return [$value, null];

            default: // string
                if (!is_string($value) && !is_int($value) && !is_float($value)) {
                    return [null, "$name must be text"];
                }
                $value = trim((string) $value);
                $max = $rule['maxLength'] ?? 2000;
                if (mb_strlen($value) > $max) {
                    return [null, "$name must be at most $max characters"];
                }
                if (isset($rule['minLength']) && mb_strlen($value) < $rule['minLength']) {
                    return [null, "$name must be at least {$rule['minLength']} characters"];
                }
                if (isset($rule['enum']) && !in_array($value, $rule['enum'], true)) {
                    return [null, "$name must be one of: " . implode(', ', $rule['enum'])];
                }
                $format = $rule['format'] ?? null;
                if ($format === 'url') {
                    if (!preg_match('#^[a-z][a-z0-9+.\-]*://#i', $value)) {
                        $value = 'https://' . $value;
                    }
                    if (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $value)) {
                        return [null, "$name must be a valid http or https URL"];
                    }
                } elseif ($format === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [null, "$name must be a valid email address"];
                }
                return [$value, null];
        }
    }
}
