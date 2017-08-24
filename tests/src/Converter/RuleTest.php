<?php

namespace Oshomo\CsvUtils\Validator;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Rules
     */
    protected $object;

    /**
     * Init Class
     */
    protected function setUp()
    {
        $this->object = new Rules();
    }

    public function testValidateMin()
    {
        $this->assertTrue($this->object->validateMin(5, 3));
        $this->assertTrue($this->object->validateMin(5, 5));
        $this->assertFalse($this->object->validateMin(3, 5));
    }

    public function testValidateMax()
    {
        $this->assertTrue($this->object->validateMax(3, 5));
        $this->assertTrue($this->object->validateMax(3, 3));
        $this->assertFalse($this->object->validateMax(5, 3));
    }

    public function testValidateUrl()
    {
        // Characters that should have been escaped?
        $this->assertFalse($this->object->validateUrl("http://stackoverflow.com/users/9999999/not a-real-user"));
        // Accidentally transposed characters?
        $this->assertFalse($this->object->validateUrl("http//:stackoverflow.com/questions/9715606/bad-url-test-cases"));
        // URL with no protocol
        $this->assertFalse($this->object->validateUrl("example.com"));
        // Correct URL with protocol and domain name
        $this->assertTrue($this->object->validateUrl("http://example.com"));
    }

}