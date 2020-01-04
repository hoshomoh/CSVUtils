<?php

namespace Oshomo\CsvUtils\Tests\src;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class UppercaseRule implements ValidationRuleInterface
{
    public function parameterCount(): int
    {
        return 0;
    }

    /**
     * @param mixed $value
     * @param $parameters
     */
    public function passes($value, array $parameters): bool
    {
        return strtoupper($value) === $value;
    }

    public function message(): string
    {
        return 'The :attribute value :value must be uppercase on line :line.';
    }

    public function parameterReplacer(string $message, array $parameters): string
    {
        return $message;
    }
}
