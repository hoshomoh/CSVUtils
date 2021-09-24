<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class MaxLength implements ValidationRuleInterface, ParameterizedRuleInterface
{
    public function allowedParameters(): array
    {
        return [':length'];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        list($length) = $parameters;

        return strlen($value) <= $length;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute value :value may not have more than :length characters on line :line.';
    }
}
