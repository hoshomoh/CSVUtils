<?php

namespace Oshomo\CsvUtils\Validator;


use Oshomo\CsvUtils\Converter\JsonConverter;
use Oshomo\CsvUtils\Converter\XmlConverter;
use Oshomo\CsvUtils\Tests\src\UppercaseRule;

class CsvValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Assets Folder Path
     */
    protected $testAssets;

    /**
     * Init Class
     */
    protected function setUp()
    {
        $this->testAssets = realpath(dirname(__FILE__) . "/../data");
    }

    public function testInvalidCsvFilePath()
    {
        $file = $this->testAssets . "/tests.csv";

        $validator = new Validator($file, ',', [
            "stars" => ["between:0,5"]
        ]);

        $this->assertSame(
            $validator::INVALID_FILE_PATH_ERROR,
            $validator->validate()['message']
        );
    }

    public function testAsciiOnlyValidationRule()
    {
        $file = $this->testAssets . "/ascii_test.csv";

        $validator = new Validator($file, ',', [
            "name" => ["ascii_only"]
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            "The name value Well Health Hotels¡ contains a non-ascii character",
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testBetweenValidationRule()
    {
        $file = $this->testAssets . "/between_test.csv";

        $validator = new Validator($file, ',', [
            "stars" => ["between:4,10"]
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            "The stars value 3 is not between 4 - 10.",
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testUrlValidationRule()
    {
        $file = $this->testAssets . "/url_test.csv";

        $validator = new Validator($file, ',', [
            "uri" => ["url"]
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            "The uri value http//:well.org is not a valid url",
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomRule()
    {
        $file = $this->testAssets . "/ascii_test.csv";

        $validator = new Validator($file, ',', [
            "name" => [new UppercaseRule]
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            "The name value Well Health Hotels¡ must be uppercase.",
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomErrorMessage()
    {
        $file = $this->testAssets . "/ascii_test.csv";
        $customErrorMessage = "The value supplied for the name attribute must only contain ascii characters";

        $validator = new Validator($file, ',', [
            "name" => ["ascii_only"]
        ], [
            "ascii_only" => $customErrorMessage
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            $customErrorMessage,
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomErrorMessageWithPlaceholder()
    {
        $file = $this->testAssets . "/between_test.csv";

        $validator = new Validator($file, ',', [
            "stars" => ["between:4,10"]
        ], [
            "between" => "The value supplied for :attribute must be between :min and :max"
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            "errors",
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            "The value supplied for stars must be between 4 and 10",
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorJsonWriter()
    {
        $file = $this->testAssets . "/valid_test.csv";

        $validator = new Validator($file, ',', [
            "name" => ["ascii_only"],
            "stars" => ["between:3,10"],
            "uri" => ["url"]
        ]);

        //$this->assertFalse($validator->fails());
        var_dump($validator->fails());
        var_dump($validator->errors());

        $this->assertTrue($validator->write(new JsonConverter()));

        $this->assertFileEquals(
            $this->testAssets . "/valid_test_expected.json",
            $this->testAssets . "/valid_test.json"
        );
    }

    public function testValidatorXmlWriter()
    {
        $file = $this->testAssets . "/valid_test.csv";

        $validator = new Validator($file, ',', [
            "name" => ["ascii_only"],
            "stars" => ["between:3,10"],
            "uri" => ["url"]
        ]);

        $this->assertFalse($validator->fails());

        $this->assertTrue($validator->write(new XmlConverter()));

        $this->assertFileEquals(
            $this->testAssets . "/valid_test_expected.xml",
            $this->testAssets . "/valid_test.xml"
        );
    }

    public function testValidatorXmlWriterWithRecordElementParameter()
    {
        $file = $this->testAssets . "/valid_test.csv";

        $validator = new Validator($file, ',', [
            "name" => ["ascii_only"],
            "stars" => ["between:3,10"],
            "uri" => ["url"]
        ]);

        $this->assertFalse($validator->fails());

        $this->assertTrue($validator->write(new XmlConverter('sample')));

        $this->assertFileEquals(
            $this->testAssets . "/valid_test_param_expected.xml",
            $this->testAssets . "/valid_test.xml"
        );
    }

}
