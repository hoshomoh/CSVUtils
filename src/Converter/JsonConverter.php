<?php
/**
 * Created by PhpStorm.
 * User: oshomo.oforomeh
 * Date: 29/01/2017
 * Time: 11:37 PM
 */

namespace Oshomo\CsvUtils\Converter;


class JsonConverter extends BaseConverter
{
    /**
     * The converted data
     *
     * @var string
     */
    private $data;

    public function convert($data)
    {
        $this->data = json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function write($filename)
    {
        if (!file_put_contents($filename, $this->data)){
            return "Data to JSON conversion not successful.";
        }

        return "Data to JSON conversion successful. Check {$filename} for your file.";
    }
}