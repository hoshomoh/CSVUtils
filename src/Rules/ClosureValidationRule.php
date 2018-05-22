<?php

namespace Oshomo\CsvUtils\Rules;


use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class ClosureValidationRule implements ValidationRuleInterface
{
    /**
     * The callback that validates the attribute.
     *
     * @var \Closure
     */
    public $callback;

    /**
     * Indicates if the validation callback failed.
     *
     * @var bool
     */
    public $failed = false;

    /**
     * The validation error message.
     *
     * @var string|null
     */
    public $message;

    /**
     * Create a new Closure based validation rule.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Determine if the validation rule accepts parameters or not.
     *
     * @return boolean
     */
    public function isImplicit()
    {
        return true;
    }

    /**
     * Get the number of parameters that should be supplied
     *
     * @return integer
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
     * @return bool
     */
    public function passes($value, $parameters)
    {
        $this->failed = false;

        $this->callback->__invoke($value, function ($message) {
            $this->failed = true;

            $this->message = $message;
        });

        return ! $this->failed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Replace error messages parameter with right values
     *
     * @param string $message
     * @param array $parameters
     * @return string
     */
    public function parameterReplacer($message, $parameters)
    {
        return $message;
    }
}
