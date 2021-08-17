<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class Numeric implements ValidationRuleInterface
{
    /**
     * Determines if the validation rule passes. This is where we do the
     * actual validation. If the validation passes return true else false.
     *
     * @param mixed $value
     * @param array $parameters
     * @param array $row
     * @return bool
     */
    public function passes($value, array $parameters, array $row): bool
    {
        if (null === $value || '' === $value) {
            return true;
        }

        return is_numeric((string)$value);
    }

    /**
     * Get the validation error message. Specify the message that should
     * be returned if the validation fails. You can make use of the
     * :attribute and :value placeholders in the message string.
     */
    public function message(): string
    {
        return 'The :attribute value :value must be a number on line :line.';
    }
}
