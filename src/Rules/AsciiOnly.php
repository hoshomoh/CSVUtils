<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class AsciiOnly implements ValidationRuleInterface
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes($value, array $parameters): bool
    {
        return (bool) mb_detect_encoding($value, 'ASCII', true);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute value :value contains a non-ascii character on line :line.';
    }
}
