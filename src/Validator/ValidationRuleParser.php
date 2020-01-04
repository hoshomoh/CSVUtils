<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Validator;

use Closure;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface as ValidationRule;
use Oshomo\CsvUtils\Rules\ClosureValidationRule;

class ValidationRuleParser
{
    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param string $rule|ValidationRuleInterface
     */
    public static function parse($rule): array
    {
        if ($rule instanceof Closure) {
            return [new ClosureValidationRule($rule), []];
        }

        if ($rule instanceof ValidationRule) {
            return [$rule, []];
        }

        return static::parseStringRule($rule);
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
        if (false !== strpos($rule, ':')) {
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
        return str_getcsv($parameter);
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
