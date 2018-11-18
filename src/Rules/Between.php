<?php

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
    public function parameterCount()
    {
        return self::PARAMETER_COUNT;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     * @param $parameters
     *
     * @return bool
     */
    public function passes($value, $parameters)
    {
        $size = (int) $value;

        return $size >= (int) $parameters[0] && $size <= (int) $parameters[1];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
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
    public function parameterReplacer($message, $parameters)
    {
        return str_replace([':min', ':max'], [$parameters[0], $parameters[1]], $message);
    }
}
