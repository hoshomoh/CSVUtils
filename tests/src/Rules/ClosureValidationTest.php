<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Tests\src\Rules;

use Oshomo\CsvUtils\Rules\ClosureValidationRule;
use PHPUnit\Framework\TestCase;

class ClosureValidationTest extends TestCase
{
    public function testParemetersCount()
    {
        $closureValidationRule = new ClosureValidationRule(function () {
            return;
        });

        $this->assertEquals(0, $closureValidationRule->parameterCount());
    }
}
