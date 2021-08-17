<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Contracts;

/**
 * Interface ArrayParameterizedRuleInterface
 * @package Oshomo\CsvUtils\Contracts
 *
 * Supports validation rules which need multiple parameters
 * that behave like an array.
 */
interface ArrayParameterizedRuleInterface
{
    /**
     * Should return an array of the allowed parameters.
     * Tha allowed parameters should be
     * tokenized string e.g :min, :max, :first, :last etc.
     */
    public function allowedParameters(): array;

    /**
     * Should return an array of parameter values as strings
     * parsed in the same order and format as allowedParameters().
     * This will aid in mapping our parameters to their placeholders.
     *
     * @param array $parameters
     * @return array
     */
    public function parseParameterValues(array $parameters): array;
}
