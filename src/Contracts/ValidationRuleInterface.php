<?php

namespace Oshomo\CsvUtils\Contracts;

interface ValidationRuleInterface
{
    /**
     * Get the number of parameters that should be supplied.
     * If no parameter should be supplied return 0 else
     * return the number of parameters that should be returned
     *
     * @return int
     */
    public function parameterCount();

    /**
     * Determines if the validation rule passes. This is where we do the
     * actual validation. If the validation passes return true else false
     *
     * @param  mixed $value
     * @param array $parameters
     *
     * @return bool
     */
    public function passes($value, $parameters);

    /**
     * Get the validation error message. Specify the message that should
     * be returned if the validation fails. You can make use of the
     * :attribute and :value placeholders in the message string
     *
     * @return string
     */
    public function message();

    /**
     * Replace error messages parameter with right values. If you want
     * to allow user pass custom placeholders in the inline message
     * specify and replace them here. If not just return $message
     * i.e return $message. But if you want to allow custom placeholder
     * return str_replace(
     *      [':custom_a', ':custom_b'],
     *      [$parameters[0], $parameters[1]],
     *      $message
     * );
     *
     * @param string $message
     * @param array $parameters
     *
     * @return string
     */
    public function parameterReplacer($message, $parameters);
}
