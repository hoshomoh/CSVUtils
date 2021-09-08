<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;
use Oshomo\CsvUtils\Helpers\ExtractsAttributeSizeAndType;

class Between implements ValidationRuleInterface, ParameterizedRuleInterface
{
    use ExtractsAttributeSizeAndType;

    private $type = 'numeric';

    public function allowedParameters(): array
    {
        return [':min', ':max'];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters, array $row): bool
    {
        $size = $this->getSize($value);

        list($min, $max) = $parameters;

        $this->type = $this->getType($value);

        return $size >= $min && $size <= $max;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'numeric' === $this->type ?
            'The :attribute value :value must be between :min and :max on line :line.' :
            'The :attribute value :value must be between :min and :max characters on line :line.';
    }
}
