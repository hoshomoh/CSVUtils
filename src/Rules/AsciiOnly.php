<?php

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class AsciiOnly implements ValidationRuleInterface
{
    /**
     * Get the number of parameters that should be supplied
     *
     * @return int
     */
    public function parameterCount()
    {
        return 0;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  mixed $value
     * @param $parameters
     *
     * @return bool
     */
    public function passes($value, $parameters)
    {
        return (mb_detect_encoding($value, 'ASCII', true)) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute value :value contains a non-ascii character";
    }

    /**
     * Replace error messages parameter with right values.
     *
     * @param string $message
     * @param array $parameters
     *
     * @return string
     */
    public function parameterReplacer($message, $parameters)
    {
        return $message;
    }
}
