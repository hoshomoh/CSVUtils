<?php

namespace Oshomo\CsvUtils\Validator;

trait MessageTrait
{

    /**
     * @param $key
     * @param $value
     * @param $attribute
     * @return string
     */
    public function getMessage($key, $value, $attribute) {
        $message = "Validation failed for {$key} rule";

        switch ($key) {
            case "min":
                $message = "{$attribute} attribute value {$value} is less than the specified minimum.";
                break;
            case "max":
                $message = "{$attribute} attribute value {$value} is greater than the specified maximum.";
                break;
            case "url":
                $message = "{$attribute} attribute value {$value} is not a valid url.";
                break;
            case "ascii":
                $message = "{$attribute} attribute value {$value} contains one or more ascii character.";
                break;
        }

        return $message;
    }

}