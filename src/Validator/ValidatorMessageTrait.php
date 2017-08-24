<?php

namespace Oshomo\CsvUtils\Validator;

trait ValidatorMessageTrait
{


    /**
     * Error message for min rule
     *
     * @param $value
     * @param $attribute
     * @return string
     */
    public function minRuleMessage($value, $attribute)
    {
        return "{$attribute} attribute value {$value} is less than the specified minimum.";
    }

    /**
     * Error message for max rule
     *
     * @param $value
     * @param $attribute
     * @return string
     */
    public function maxRuleMessage($value, $attribute)
    {
        return "{$attribute} attribute value {$value} is greater than the specified maximum.";
    }

    /**
     * Error message for url rule
     *
     * @param $value
     * @param $attribute
     * @return string
     */
    public function urlRuleMessage($value, $attribute)
    {
        return "{$attribute} attribute value {$value} is not a valid url.";
    }

    /**
     * Error message for ascii rule
     *
     * @param $value
     * @param $attribute
     * @return string
     */
    public function asciiRuleMessage($value, $attribute)
    {
        return "{$attribute} attribute value {$value} contains one or more ascii character.";
    }

    /**
     * @param $key
     * @param $value
     * @param $attribute
     * @return string
     */
    public function getMessage($key, $value, $attribute) 
    {
        $message = "Validation failed for {$key} rule";
        $messageRuleMethodName = $key."RuleMessage";

        if (method_exists($this, $messageRuleMethodName) && 
            is_callable(array($this, $messageRuleMethodName))) {
            $message = $this->$messageRuleMethodName($value, $attribute);
        }

        return $message;
    }

}