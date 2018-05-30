<?php

namespace Oshomo\CsvUtils\Contracts;

interface ConverterHandlerInterface
{
    /**
     * Get the converter file extension. If the file extension
     * for this converter is csv just return "csv"
     *
     * @return string
     */
    public function getExtension();

    /**
     * Does the actual conversion. This is where the actual conversion
     * is carried out.
     *
     * @param array $data
     *
     * @return $this
     */
    public function convert($data);

    /**
     * Writes the converted data to the path specified
     *
     * @param string $filename
     *
     * @return bool
     */
    public function write($filename);
}
