<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Contracts;

interface ParameterizedRuleInterface
{
    /**
     * Should return an array of the allowed parameters.
     * See the between rule. Tha allowed parameters should be
     * tokenized string e.g :min, :max, :first, :last etc.
     */
    public function allowedParameters(): array;
}
