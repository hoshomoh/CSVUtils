<?php

namespace Oshomo\CsvUtils\Converter;

interface ConverterHandlerInterface {

    /**
     * @param $data
     * @return mixed
     */
    public function convert($data);

    /**
     * @param $filename
     * @return mixed
     */
    public function write($filename);
}