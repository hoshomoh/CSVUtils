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
     *
     * @param mixed $value
     */
    public function passes($value, array $parameters): bool
    {
        $size = (int) $value;

        return $size >= (int) $parameters[0] && $size <= (int) $parameters[1];
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute value :value is not between :min - :max on line :line.';
    }
}
