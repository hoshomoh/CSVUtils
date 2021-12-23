<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ArrayParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class RequiredIf implements ValidationRuleInterface, ArrayParameterizedRuleInterface
{
    /**
     * Should return an array of the allowed parameters.
     * The allowed parameters should be tokenized string
     * e.g :min, :max, :first, :last etc.
     */
    public function allowedParameters(): array
    {
        // required_if validation uses a dependent field
        // marked as :other_field and an array of values
        // which are allowed for the dependent field marked
        // as :other_values
        return [':other_field', ':other_values'];
    }

    /**
     * Should return an array of parameter values as strings
     * parsed in the same order and format as allowedParameters().
     * This will aid in mapping our parameters to their placeholders.
     */
    public function parseParameterValues(array $parameters): array
    {
        return [array_shift($parameters), implode(',', $parameters)];
    }

    /**
     * Determines if the validation rule passes. This is where we do the
     * actual validation. If the validation passes return true else false.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        $otherField = array_shift($parameters);
        $otherValues = $parameters;

        if (in_array($row[$otherField], $otherValues)) {
            return null !== $value && '' !== $value;
        }

        return true;
    }

    /**
     * Get the validation error message. Specify the message that should
     * be returned if the validation fails. You can make use of the
     * :attribute and :value placeholders in the message string.
     */
    public function message(): string
    {
        return 'The :attribute field is required when :other_field is :other_values on line :line.';
    }
}
