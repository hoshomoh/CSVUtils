<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class AsciiOnly implements ValidationRuleInterface
{
    /**
     * Get the number of parameters that should be supplied.
     *
     * @return int
     */
    public function parameterCount(): int
    {
        return 0;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     * @param array $parameters
     *
     * @return bool
     */
    public function passes($value, array $parameters): bool
    {
        return (mb_detect_encoding($value, 'ASCII', true)) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute value :value contains a non-ascii character on line :line.';
    }

    /**
     * Replace error messages parameter with right values.
     *
     * @param string $message
     * @param array $parameters
     *
     * @return string
     */
    public function parameterReplacer(string $message, array $parameters): string
    {
        return $message;
    }
}
