<?php

namespace Oshomo\CsvUtils\Validator;

use InvalidArgumentException;

class CsvValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Assets Folder Path
     */
    protected $test_assets;

    /**
     * @var CsvValidator
     */
    protected $validator;

    /**
     * Init Class
     */
    protected function setUp()
    {
        $this->test_assets = realpath(dirname(__FILE__) . "/../../files");
        $this->validator = new CsvValidator();
        $this->validator->setFilePath($this->test_assets . "/test.csv");
    }

    public function testConstructorParams()
    {   
        $file = $this->test_assets . "/test.csv";
        $rule = [
            "name" => ["ascii" => ""]
        ];
        $validator = new CsvValidator($file, $rule);
        $this->assertEquals($file, $validator->getFilePath());
        $this->assertNotEmpty($validator->getRules());
    }

    public function testWrongFilePathException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->validator->setFilePath($this->test_assets . "/tet.csv")->validate();
    }

    public function testMessageTraitMessages()
    {
        $this->assertEquals("ratings attribute value 3 is less than the specified minimum.", $this->validator->getMessage("min", 3, "ratings"));
        $this->assertEquals("stars attribute value 5 is greater than the specified maximum.", $this->validator->getMessage("max", 5, "stars"));
        $this->assertEquals("website attribute value http//:test.com is not a valid url.", $this->validator->getMessage("url", "http//:test.com", "website"));
        $this->assertEquals("name attribute value test ascii string contains one or more ascii character.", $this->validator->getMessage("ascii", "test ascii string", "name"));
    }

    public function testGetHeadersBeforeValidate()
    {
        $this->validator->setRules([]);

        $this->assertEmpty($this->validator->getHeaders());
    }

    public function testGetHeadersAfterValidate()
    {
        $this->validator->setRules([])->validate();

        $this->assertEquals(['name', 'address', 'stars', 'contact', 'uri'], $this->validator->getHeaders());
    }

    public function testValidate()
    {
        $this->validator->setRules(
            [
                "stars" => ["min" => 4],
                "uri" => ["url" => ""]
            ]
        )->validate();

        $this->assertEquals(2, count($this->validator->getAllData()));
        $this->assertEquals(1, count($this->validator->getInvalidData()));
        $this->assertEquals(1, count($this->validator->getValidData()));
        $this->assertEquals(1, count($this->validator->getInValidData()[0]['errors']));
        $this->assertEquals("uri attribute value http//:well.org is not a valid url.", $this->validator->getInValidData()[0]['errors'][0]);
    }

}