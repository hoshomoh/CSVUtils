<?php

namespace Oshomo\CsvUtils;

use InvalidArgumentException;
use Oshomo\CsvUtils\Converter\CsvConverter;
use Oshomo\CsvUtils\Validator\CsvValidator;

class CsvConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Assets Folder Path
     */
    protected $test_assets;

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
        $this->test_assets = realpath(dirname(__FILE__) . "/../../files");
        $validator = new CsvValidator($this->test_assets . "/test.csv");
        $this->converter = new CsvConverter([], $this->test_assets);
        $this->object = $validator->validate()->getAllData();
    }

    public function testConstructorParams()
    {
        $this->assertEquals($this->test_assets, $this->converter->getPath());
        $this->assertEmpty($this->converter->getData());
    }

    public function testInvalidDirectoryPathException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->setPath($this->test_assets . "/test.csv");
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
        $this->assertFileExists($this->test_assets . "/test.json");
        $this->assertFileEquals($this->test_assets . "/test-expected.json", $this->test_assets . "/test.json");
    }

    public function testToXml()
    {
        $this->converter->setData($this->object);
        $this->converter->toXml("test.xml");
        $this->assertFileExists($this->test_assets . "/test.xml");
        $this->assertFileEquals($this->test_assets . "/test-expected.xml", $this->test_assets . "/test.xml");
    }

}