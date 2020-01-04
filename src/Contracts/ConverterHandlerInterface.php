<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Contracts;

interface ConverterHandlerInterface
{
    /**
     * Get the converter file extension. If the file extension
     * for this converter is csv just return "csv".
     */
    public function getExtension(): string;

    /**
     * Does the actual conversion. This is where the actual conversion
     * is carried out.
     */
    public function convert(array $data): self;

    /**
     * Writes the converted data to the path specified.
     */
    public function write(string $filename): bool;
}
