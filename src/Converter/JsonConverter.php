<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Converter;

use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;

class JsonConverter implements ConverterHandlerInterface
{
    public const FILE_EXTENSION = 'json';

    /**
     * The converted data.
     *
     * @var string
     */
    protected $data;

    public function getExtension(): string
    {
        return self::FILE_EXTENSION;
    }

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

    public function write(string $filename): bool
    {
        return (bool) file_put_contents($filename, $this->data);
    }
}
