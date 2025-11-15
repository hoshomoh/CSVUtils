<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Validator;

use Closure;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface as ValidationRule;
use Oshomo\CsvUtils\Rules\ClosureValidationRule;

class ValidationRuleParser
{
    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param int|string
     * @param string|ValidationRuleInterface $rule
     */
    public static function parse($ruleKey, $ruleValue): array
    {
        if ($ruleValue instanceof Closure) {
            return [new ClosureValidationRule($ruleValue), []];
        }

        if ($ruleValue instanceof ValidationRule) {
            return [$ruleValue, []];
        }

        return ValidationRuleParser::parseRule($ruleKey, $ruleValue);
    }

    protected static function stringRuleHasParameter(string $rule): bool
    {
        return false !== strpos($rule, ':');
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

    /**
     * Parse lib defined rule.
     *
     * @param int|string                    $ruleKey
     * @param string|Closure|ValidationRule $ruleValue
     */
    protected static function parseRule($ruleKey, $ruleValue): array
    {
        $rule = '';
        $parameters = [];

        if (is_int($ruleKey) && is_string($ruleValue)) {
            // This will match "rule", "rule:value", "rule:value1,value2"
            $rule = $ruleValue;

            if (ValidationRuleParser::stringRuleHasParameter($rule)) {
                list($rule, $ruleParameters) = explode(':', $ruleValue);

                $parameters = static::parseParameters($ruleParameters);
            }
        } elseif (is_string($ruleKey) && !static::stringRuleHasParameter($ruleKey)) {
        }

        return [ValidationRuleParser::normalizeRule($rule), $parameters];
    }
}
