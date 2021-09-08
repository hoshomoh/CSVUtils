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

    public function testAlphaValidationRule()
    {
        $file = $this->testAssets . '/alpha_num_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['alpha'],
            'contact' => ['alpha'],
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
            'The name value Well Health Hotels may only contain letters on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testAlphaNumValidationRule()
    {
        $file = $this->testAssets . '/alpha_num_test.csv';

        $validator = new Validator($file, ',', [
            'address' => ['alpha_num'],
            'contact' => ['alpha_num'],
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
            'The address value Inga N. P.O. Box 567 may only contain letters and numbers on line 2.',
            $validator->errors()['data'][0]['errors']
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
            'name' => ['between:5,15'],
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
            'The name value Well Health Hotels must be between 5 and 15 characters on line 2.',
            $validator->errors()['data'][0]['errors']
        );

        $this->assertContains(
            'The stars value 3 must be between 4 and 10 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testInValidationRule()
    {
        $file = $this->testAssets . '/in_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['in:3,5,8,10'],
            'contact' => ['in:Kasper Zen'],
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
            'The stars value 2 does not exist in 3,5,8,10 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testIntegerValidationRule()
    {
        $file = $this->testAssets . '/integer_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['integer'],
            'id' => ['integer'],
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
            'The stars value 5.5 must be an integer on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testMaxValidationRule()
    {
        $file = $this->testAssets . '/min_max_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['max:10'],
            'stars' => ['max:1'],
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
            'The name value Well Health Hotels may not be greater than 10 characters on line 2.',
            $validator->errors()['data'][0]['errors']
        );

        $this->assertContains(
            'The stars value 3 may not be greater than 1 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testMinValidationRule()
    {
        $file = $this->testAssets . '/min_max_test.csv';

        $validator = new Validator($file, ',', [
            'name' => ['min:30'],
            'stars' => ['min:4'],
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
            'The name value Well Health Hotels may not be less than 30 characters on line 2.',
            $validator->errors()['data'][0]['errors']
        );

        $this->assertContains(
            'The stars value 3 may not be less than 4 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testNumericValidationRule()
    {
        $file = $this->testAssets . '/numeric_test.csv';

        $validator = new Validator($file, ',', [
            'stars' => ['numeric'],
            'id' => ['numeric'],
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
            'The stars value A must be a number on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testRequiredIfValidationRule()
    {
        $file = $this->testAssets . '/required_if_test.csv';

        $validator = new Validator($file, ',', [
            'contact' => ['required_if:address,Inga N. P.O. Box 567'],
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
            'The contact field is required when address is Inga N. P.O. Box 567 on line 2.',
            $validator->errors()['data'][0]['errors']
        );
    }

    public function testRequiredValidationRule()
    {
        $file = $this->testAssets . '/required_test.csv';

        $validator = new Validator($file, ',', [
            'address' => ['required'],
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
            'The address value is required on line 2.',
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

        $this->assertArrayHasKey(
            'errors',
            $validationErrors['data'][0]
        );

        $this->assertContains(
            'The uri value http//:well.org is not a valid url on line 2.',
            $validationErrors['data'][0]['errors']
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
            'uri' => [function ($value, $row, $fail) {
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
