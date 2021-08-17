<?php

namespace Oshomo\CsvUtils\Tests\src;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class UppercaseRule implements ValidationRuleInterface
{
    /**
     * @param mixed $value
     * @param array $parameters
     * @param array $row
     * @return bool
     */
    public function passes($value, array $parameters, array $row): bool
    {
        return strtoupper($value) === $value;
    }

    public function message(): string
    {
        return 'The :attribute value :value must be uppercase on line :line.';
    }
}
