<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class Between implements ValidationRuleInterface, ParameterizedRuleInterface
{
    public function allowedParameters(): array
    {
        return [':min', ':max'];
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($value, array $parameters, array $row): bool
    {
        list($min, $max) = $parameters;

        if (is_numeric($value)) {
            $convertedValue = +$value;

            return $convertedValue >= +$min && $convertedValue <= +$max;
        }

        if (is_string($value)) {
            $valueLength = strlen($value);

            return $valueLength >= +$min && $valueLength <= +$max;
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute value :value is not between :min - :max on line :line.';
    }
}
