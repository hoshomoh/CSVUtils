<?php

require 'vendor/autoload.php';

use Oshomo\CsvUtils\Validator\CsvValidator;
use Oshomo\CsvUtils\Converter\CsvConverter;

$file_path = realpath(dirname(__FILE__));
$file = $file_path . "/sample.csv";

$validator = new CsvValidator($file, [
    "name" => ["ascii" => ""],
    "uri"   => ["url" => ""],
    "stars" => ["min" => 0, "max" => 5]
]);

try {
    $data = $validator->validate()->getValidData();
    $converter = new CsvConverter($file_path, $data);
    echo $converter->toJson("sample.json");
    echo $converter->toXml("sample.xml");
}catch (Exception $exception) {
    echo $exception->getMessage();
}

