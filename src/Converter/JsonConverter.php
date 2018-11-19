<?php

declare(strict_types=1);

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
    public function getExtension(): string
    {
        return self::FILE_EXTENSION;
    }

    /**
     * @param array $data
     *
     * @return ConverterHandlerInterface
     */
    public function convert(array $data): ConverterHandlerInterface
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
    public function write(string $filename): bool
    {
        return (file_put_contents($filename, $this->data)) ? true : false;
    }
}
