<?php

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;
use Oshomo\CsvUtils\Helpers\ExtractsAttributeSizeAndType;

class Min implements ValidationRuleInterface, ParameterizedRuleInterface
{
    use ExtractsAttributeSizeAndType;

    private $type = 'numeric';

    public function allowedParameters(): array
    {
        return [':min'];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        list($min) = $parameters;

        $this->type = $this->getType($value);

        return $this->getSize($value) >= $min;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'numeric' === $this->type ?
            'The :attribute value :value may not be less than :min on line :line.' :
            'The :attribute value :value may not be less than :min characters on line :line.';
    }
}
