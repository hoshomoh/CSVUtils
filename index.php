<?php

require 'vendor/autoload.php';

use Oshomo\CsvUtils\Validator\CsvValidator;
use Oshomo\CsvUtils\Converter\CsvConverter;

$file_path = realpath(dirname(__FILE__));
$file = $file_path . "/sample.csv";

$validator = new CsvValidator($file, [
    "name" => ["ascii" => ""],
    "uri"   => ["url" => ""],
    "stars" => ["min" => 7, "max" => 10]
]);

try {
    $data = $validator->validate()->getValidData();
    if($data) {
	    $converter = new CsvConverter($data, $file_path);
	    echo $converter->toJson("sample.json") . "\n";
	    echo $converter->toXml("sample.xml");
	}
}catch (Exception $exception) {
    echo $exception->getMessage();
}

