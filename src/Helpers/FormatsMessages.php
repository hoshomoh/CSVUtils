<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Helpers;

use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

trait FormatsMessages
{
    /**
     * Get the validation message for an attribute and rule.
     *
     * @param string                  $attribute
     * @param ValidationRuleInterface $rule
     * @param string                  $actualRule
     *
     * @return string
     */
    protected function getMessage(string $attribute, ValidationRuleInterface $rule, string $actualRule): string
    {
        $inlineMessage = $this->getInlineMessage($attribute, $actualRule);

        if (!is_null($inlineMessage)) {
            return $inlineMessage;
        }

        return $rule->message();
    }

    /**
     * Get the proper inline error message passed to the validator.
     *
     * @param string $attribute
     * @param string $rule
     *
     * @return string|null
     */
    protected function getInlineMessage(string $attribute, string $rule): ?string
    {
        return $this->getFromLocalArray($attribute, $this->ruleToLower($rule));
    }

    /**
     * Get the inline message for a rule if it exists.
     *
     * @param string $attribute
     * @param string $lowerRule
     *
     * @return string|null
     */
    protected function getFromLocalArray(string $attribute, string $lowerRule): ?string
    {
        $source = $this->customMessages;

        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];

        foreach ($keys as $key) {
            foreach (array_keys($source) as $sourceKey) {
                if ($sourceKey === $key) {
                    return $source[$sourceKey];
                }
            }
        }

        return null;
    }

    /**
     * @param string $rule
     *
     * @return string|string[]|null
     */
    protected function ruleToLower(string $rule): ?string
    {
        $lowerRule = preg_replace('/[A-Z]/', '_$0', $rule);

        $lowerRule = strtolower($lowerRule);

        $lowerRule = ltrim($lowerRule, '_');

        return $lowerRule;
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param string                  $message
     * @param string                  $attribute
     * @param mixed                   $value
     * @param ValidationRuleInterface $rule
     * @param array                   $parameters
     * @param int                     $lineNumber
     *
     * @return string
     */
    protected function makeReplacements(string $message, string $attribute, $value, ValidationRuleInterface $rule, array $parameters, int $lineNumber): string
    {
        $message = $this->replaceAttributePlaceholder($message, $attribute);

        $message = $this->replaceValuePlaceholder($message, $value);

        $message = $this->replaceErrorLinePlaceholder($message, $lineNumber);

        $message = $rule->parameterReplacer($message, $parameters);

        return $message;
    }

    /**
     * Replace the :attribute placeholder in the given message.
     *
     * @param string $message
     * @param string $attribute
     *
     * @return string
     */
    protected function replaceAttributePlaceholder(string $message, string $attribute): string
    {
        return str_replace([':attribute'], [$attribute], $message);
    }

    /**
     * Replace the :value placeholder in the given message.
     *
     * @param string $message
     * @param string $value
     *
     * @return string
     */
    protected function replaceValuePlaceholder(string $message, string $value): string
    {
        return str_replace([':value'], [$value], $message);
    }

    /**
     * Replace the :line placeholder in the given message.
     *
     * @param string $message
     * @param int    $lineNumber
     *
     * @return mixed
     */
    protected function replaceErrorLinePlaceholder(string $message, int $lineNumber)
    {
        return str_replace([':line'], [$lineNumber], $message);
    }
}
