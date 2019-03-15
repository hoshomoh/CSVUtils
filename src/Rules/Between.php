<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class Between implements ValidationRuleInterface
{
    const PARAMETER_COUNT = 2;

    /**
     * Get the number of parameters that should be supplied.
     *
     * @return int
     */
    public function parameterCount(): int
    {
        return self::PARAMETER_COUNT;
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
        $size = (int) $value;

        return $size >= (int) $parameters[0] && $size <= (int) $parameters[1];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute value :value is not between :min - :max on line :line.';
    }

    /**
     * Replace error messages parameter with right values.
     *
     * @param string $message
     * @param array  $parameters
     *
     * @return string
     */
    public function parameterReplacer(string $message, array $parameters): string
    {
        return str_replace([':min', ':max'], [$parameters[0], $parameters[1]], $message);
    }
}
