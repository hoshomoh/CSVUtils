<?php

namespace Oshomo\CsvUtils\Tests\src;

use Oshomo\CsvUtils\Validator\ValidationRuleParser;
use PHPUnit\Framework\TestCase;

class CsvValidatorParserTest extends TestCase
{
    public function testWhenCustomRuleIsPassed()
    {
        $customRule = new UppercaseRule();

        $this->assertSame(
            [$customRule, []],
            ValidationRuleParser::parse($customRule)
        );
    }

    public function testWhenOtherRulesArePassed()
    {
        $this->assertSame(
            ['AsciiOnly', []],
            ValidationRuleParser::parse('ascii_only')
        );
    }

    public function testWhenRulesAcceptParameters()
    {
        $this->assertSame(
            ['Between', ['1', '3']],
            ValidationRuleParser::parse('between:1,3')
        );
    }
}
