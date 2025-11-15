<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Rules;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class ClosureValidationRule implements ValidationRuleInterface
{
    /**
     * The callback that validates the attribute.
     */
    public \Closure $callback;

    /**
     * Indicates if the validation callback failed.
     */
    public bool $failed = false;

    /**
     * The validation error message.
     */
    public ?string $message;

    /**
     * Create a new Closure based validation rule.
     */
    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($value, array $parameters, array $row): bool
    {
        $this->callback->__invoke($value, function ($message) {
            $this->failed = true;

            $this->message = $message;
        });

        return !$this->failed;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->message;
    }
}
