<?php

namespace Oshomo\CsvUtils\Validator;

use Oshomo\CsvUtils\Converter\JsonConverter;
use Oshomo\CsvUtils\Converter\XmlConverter;
use Oshomo\CsvUtils\Tests\src\UppercaseRule;
use PHPUnit\Framework\TestCase;

class CsvValidatorTest extends TestCase
{
    /**
     * Test Assets Folder Path.
     */
    protected $testAssets;

    /**
     * Init Class.
     */
    protected function setUp(): void
    {
        $this->testAssets = realpath(dirname(__FILE__) . '/../data');
    }

    public function testInvalidCsvFilePath()
    {
        $file = $this->testAssets . '/tests.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['between:0,5'],
        ]);

        $this->assertSame(
            $validator::INVALID_FILE_PATH_ERROR,
            $validator->validate()['message']
        );
    }

    public function testAsciiOnlyValidationRule()
    {
        $file = $this->testAssets . '/ascii_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['ascii_only'],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            'The name value Well Health Hotels¡ contains a non-ascii character on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testBetweenValidationRule()
    {
        $file = $this->testAssets . '/between_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['between:4,10'],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            'The stars value 3 is not between 4 - 10 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testUrlValidationRule()
    {
        $file = $this->testAssets . '/url_test.csv';

        $validator = new Validator($file, ',', [
            'uri' => ['url'],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $validationErrors = $validator->errors();

        for ($csvRow = 0; $csvRow < 3; ++$csvRow) {
            $this->assertArrayHasKey(
                'errors',
                $validationErrors['data'][$csvRow]
            );
        }

        $this->assertContains(
            'The uri value http//:well.org is not a valid url on line 2.',
            $validationErrors['data'][0]['errors']
        );

        $this->assertContains(
            'The uri value  is not a valid url on line 3.',
            $validationErrors['data'][1]['errors']
        );

        $this->assertContains(
            'The uri value  is not a valid url on line 4.',
            $validationErrors['data'][2]['errors']
        );
    }

    public function testValidatorWithCustomRuleObject()
    {
        $file = $this->testAssets . '/ascii_test.csv';

        $validator = new Validator($file, ',', [
            'name' => [new UppercaseRule()],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            'The name value Well Health Hotels¡ must be uppercase on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomRuleClosure()
    {
        $file = $this->testAssets . '/url_test.csv';

        $validator = new Validator($file, ',', [
            'uri' => [function ($value, $fail) {
                if (0 !== strpos($value, 'https://')) {
                    return $fail('The URL passed must be https i.e it must start with https://');
                }
            }],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            'The URL passed must be https i.e it must start with https://',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomErrorMessage()
    {
        $file = $this->testAssets . '/ascii_test.csv';
        $customErrorMessage = 'The value supplied for the name attribute must only contain ascii characters';

        $validator = new Validator($file, ',', [
            'name' => ['ascii_only'],
        ], [
            'ascii_only' => $customErrorMessage,
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            $customErrorMessage,
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorWithCustomErrorMessageWithPlaceholder()
    {
        $file = $this->testAssets . '/between_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['between:4,10'],
        ], [
            'between' => 'The value supplied for :attribute must be between :min and :max',
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            $validator::ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertArrayHasKey(
            'errors',
            $validator->errors()['data'][0]
        );

        $this->assertContains(
            'The value supplied for stars must be between 4 and 10',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testValidatorJsonWriter()
    {
        $file = $this->testAssets . '/valid_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['ascii_only'],
            'stars' => ['between:3,10'],
            'uri' => ['url'],
        ]);

        $this->assertFalse($validator->fails());

        $this->assertSame(
            $validator::NO_ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertTrue($validator->write(new JsonConverter()));

        $this->assertFileEquals(
            $this->testAssets . '/valid_test_expected.json',
            $this->testAssets . '/valid_test.json'
        );
    }

    public function testValidatorXmlWriter()
    {
        $file = $this->testAssets . '/valid_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['ascii_only'],
            'stars' => ['between:3,10'],
            'uri' => ['url'],
        ]);

        $this->assertFalse($validator->fails());

        $this->assertSame(
            $validator::NO_ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertTrue($validator->write(new XmlConverter()));

        $this->assertFileEquals(
            $this->testAssets . '/valid_test_expected.xml',
            $this->testAssets . '/valid_test.xml'
        );
    }

    public function testValidatorCsvOnEmptyRule()
    {
        $file = $this->testAssets . '/valid_test.csv';

        $expectedArray = [
            'message' => 'CSV is valid.',
            'data' => [
                [
                    'name' => 'Well Health Hotels',
                    'address' => 'Inga N. P.O. Box 567',
                    'stars' => '3',
                    'contact' => 'Kasper Zen',
                    'uri' => 'http://well.org',
                ],
            ],
        ];

        $validator = new Validator($file, ',', [
            'stars' => [''],
        ]);

        $this->assertSame($expectedArray, $validator->validate());
    }

    public function testValidatorCsvIsValid()
    {
        $file = $this->testAssets . '/valid_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['between:3,10'],
        ]);

        $expectedArray = [
            'message' => 'CSV is valid.',
            'data' => [
                [
                    'name' => 'Well Health Hotels',
                    'address' => 'Inga N. P.O. Box 567',
                    'stars' => '3',
                    'contact' => 'Kasper Zen',
                    'uri' => 'http://well.org',
                ],
            ],
        ];

        $this->assertSame($expectedArray, $validator->validate());
    }

    public function testValidatorXmlWriterWithRecordElementParameter()
    {
        $file = $this->testAssets . '/valid_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['ascii_only'],
            'stars' => ['between:3,10'],
            'uri' => ['url'],
        ]);

        $this->assertFalse($validator->fails());

        $this->assertSame(
            $validator::NO_ERROR_MESSAGE,
            $validator->errors()['message']
        );

        $this->assertTrue($validator->write(new XmlConverter('sample')));

        $this->assertFileEquals(
            $this->testAssets . '/valid_test_param_expected.xml',
            $this->testAssets . '/valid_test.xml'
        );
    }
}
