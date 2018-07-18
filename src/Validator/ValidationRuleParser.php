<?php

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
     *
     * @return array
     */
    public static function parse($rule)
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
     *
     * @param string $rule
     *
     * @return array
     */
    protected static function parseStringRule($rule)
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Between:3,5" states that the value may only be between 3 - 5.
        if (strpos($rule, ':') !== false) {
            list($rule, $parameter) = explode(':', $rule, 2);

            $parameters = static::parseParameters($parameter);
        }

        return [static::normalizeRule($rule), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param string $parameter
     *
     * @return array
     */
    protected static function parseParameters($parameter)
    {
        return str_getcsv($parameter);
    }

    /**
     * Normalizes a rule.
     *
     * @param string $rule
     *
     * @return string
     */
    protected static function normalizeRule($rule)
    {
        $rule = ucwords(str_replace(['-', '_'], ' ', $rule));

        return preg_replace('/\s/', '', $rule);
    }
}
