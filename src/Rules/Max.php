<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class Max implements ValidationRuleInterface, ParameterizedRuleInterface
{
    public function allowedParameters(): array
    {
        return [':max'];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        list($max) = $parameters;

        return +$value <= +$max;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute value :value may not be greater than :max on line :line.';
    }
}
