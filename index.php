<?php

require 'vendor/autoload.php';

use Oshomo\CsvUtils\Converter\JsonConverter;
use Oshomo\CsvUtils\Converter\XmlConverter;
use Oshomo\CsvUtils\Validator\Validator;

$file_path = realpath(dirname(__FILE__));
$file = $file_path . '/sample/sample.csv';
$validator = new Validator($file, ',', [
    'stars' => ['between:7,10'],
    'name' => ['ascii_only'],
    'uri' => ['url', function ($value, $fail) {
        if (0 !== strpos($value, 'https://')) {
            return $fail('The URL passed must be https i.e it must start with https://');
        }
    }],
]);

if (!$validator->fails()) {
    $validator->write(new JsonConverter());
    $validator->write(new XmlConverter());
} else {
    $validator->write(new JsonConverter());
    $validator->write(new XmlConverter('hotel'));
    print_r($validator->errors());
}
