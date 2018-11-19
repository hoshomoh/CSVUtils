<?php

declare(strict_types=1);

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
     * @param \Closure $callback
     */
    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

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
        $this->failed = false;

        $this->callback->__invoke($value, function ($message) {
            $this->failed = true;

            $this->message = $message;
        });

        return !$this->failed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
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
