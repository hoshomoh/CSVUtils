<?php

namespace Oshomo\CsvUtils\Converter;

use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;

class JsonConverter implements ConverterHandlerInterface
{
    const FILE_EXTENSION = 'json';

    /**
     * The converted data.
     *
     * @var string
     */
    protected $data;

    /**
     * @return string
     */
    public function getExtension()
    {
        return self::FILE_EXTENSION;
    }

    /**
     * @param array $data
     *
     * @return $this|mixed
     */
    public function convert($data)
    {
        $this->data = json_encode(
            $data,
            JSON_PRETTY_PRINT |
            JSON_NUMERIC_CHECK |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function write($filename)
    {
        return (file_put_contents($filename, $this->data)) ? true : false;
    }
}
