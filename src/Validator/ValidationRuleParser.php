<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Validator;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface as ValidationRule;
use Oshomo\CsvUtils\Rules\ClosureValidationRule;

class ValidationRuleParser
{
    /**
     * Extract the rule name and parameters from a rule.
     */
    public static function parse(\Closure|ValidationRule|array|string $rule): array
    {
        if ($rule instanceof \Closure) {
            return [new ClosureValidationRule($rule), []];
        }

        if ($rule instanceof ValidationRule) {
            return [$rule, []];
        }

        if (is_array($rule)) {
            return static::parseTupleRule($rule);
        }

        return static::parseStringRule($rule);
    }

    /**
     * Parse a tuple-based rule: ['between', '50,90'] or ['between', ['50', '90']].
     */
    protected static function parseTupleRule(array $ruleTuple): array
    {
        $rule = (string) ($ruleTuple[0] ?? '');
        $parameters = [];

        if (array_key_exists(1, $ruleTuple)) {
            $raw = $ruleTuple[1];

            if (is_string($raw)) {
                $parameters = static::parseParameters($raw);
            } elseif (is_array($raw)) {
                $parameters = $raw;
            }
        }

        return [static::normalizeRule($rule), $parameters];
    }

    /**
     * Parse a string based rule.
     */
    protected static function parseStringRule(string $rule): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Between:3,5" states that the value may only be between 3 - 5.
        if (str_contains($rule, ':')) {
            list($rule, $parameter) = explode(':', $rule, 2);

            $parameters = static::parseParameters($parameter);
        }

        return [static::normalizeRule($rule), $parameters];
    }

    /**
     * Parse a parameter list.
     */
    protected static function parseParameters(string $parameter): array
    {
        // Parse CSV first
        $values = str_getcsv($parameter);

        // Then trim each value
        return array_map('trim', $values);
    }

    /**
     * Normalizes a rule.
     */
    protected static function normalizeRule(string $rule): string
    {
        $rule = ucwords(str_replace(['-', '_'], ' ', $rule));

        return preg_replace('/\s/', '', $rule);
    }
}
