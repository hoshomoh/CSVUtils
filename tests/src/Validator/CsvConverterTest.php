<?php

namespace Oshomo\CsvUtils;

use InvalidArgumentException;
use Oshomo\CsvUtils\Converter\CsvConverter;
use Oshomo\CsvUtils\Validator\CsvValidator;

class CsvConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CsvConverter
     */
    protected $converter;

    /**
     * @var object
     */
    protected $object;

    /**
     * Init Class
     */
    protected function setUp()
    {
        $validator = new CsvValidator("tests/files/test.csv");
        $this->converter = new CsvConverter("tests/files");
        $this->object = $validator->validate()->getAllData();
    }

    public function testConstructorParams()
    {
        $this->assertEquals("tests/files", $this->converter->getPath());
        $this->assertEmpty($this->converter->getData());
    }

    public function testInvalidDirectoryPathException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->setPath("tests/files/test.csv");
    }

    public function testSetData()
    {
        $this->converter->setData($this->object);
        $this->assertNotEmpty($this->converter->getData());
    }

    public function testToJson()
    {
        $this->converter->setData($this->object);
        $this->converter->toJson("test.json");
        $this->assertFileExists('tests/files/test.json');
        $this->assertFileEquals('tests/files/test-expected.json', 'tests/files/test.json');
    }

    public function testToXml()
    {
        $this->converter->setData($this->object);
        $this->converter->toXml("test.jso");
        $this->assertFileExists('tests/files/test.xml');
        $this->assertFileEquals('tests/files/test-expected.xml', 'tests/files/test.xml');
    }

}