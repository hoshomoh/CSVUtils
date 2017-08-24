<?php
/**
 * Created by PhpStorm.
 * User: oshomo.oforomeh
 * Date: 30/01/2017
 * Time: 6:53 PM
 */

namespace Oshomo\CsvUtils\Converter;


abstract class BaseConverter implements ConverterHandlerInterface
{

    /**
     * @param $data
     * @return mixed
     */
    public abstract function convert($data);

    /**
     * @param $filename
     * @return mixed
     */
    public abstract function write($filename);
}