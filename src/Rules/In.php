<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ArrayParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class In implements ValidationRuleInterface, ArrayParameterizedRuleInterface
{
    /**
     * Determines if the validation rule passes. This is where we do the
     * actual validation. If the validation passes return true else false.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        if (null === $value || '' === $value) {
            return true;
        }

        return in_array($value, $parameters);
    }

    /**
     * Get the validation error message. Specify the message that should
     * be returned if the validation fails. You can make use of the
     * :attribute and :value placeholders in the message string.
     */
    public function message(): string
    {
        return 'The :attribute value :value does not exist in :allowed_values on line :line.';
    }

    /**
     * Should return an array of the allowed parameters.
     * The allowed parameters should be tokenized string
     * e.g :min, :max, :first, :last etc.
     */
    public function allowedParameters(): array
    {
        // all parameter values passed to the in rule will
        // get mapped as allowed_values
        // example in:1,2,3,4
        // the values [1, 2, 3, 4] will get mapped to :allowed_values
        // while checking for passes() and while writing message
        // using parseParameterValues()
        return [':allowed_values'];
    }

    /**
     * Should return an array of parameter values as strings
     * parsed in the same order and format as allowedParameters().
     * This will aid in mapping our parameters to their placeholders.
     */
    public function parseParameterValues(array $parameters): array
    {
        // make sure to convert the values to an imploded string
        // this will then get mapped to array index 0 which has
        // the placeholder for :allowed_values
        return [implode(',', $parameters)];
    }
}
