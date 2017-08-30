<?php

namespace Oshomo\CsvUtils\Converter;

use InvalidArgumentException;

class CsvConverter
{
    /**
     * The path where the generated file should be stored
     *
     * @var string
     */
    protected $path;

    /**
     * The data to be converted
     *
     * @var string
     */
    protected $data;

    /**
     * Create a new CSV converter instance.
     *
     * @param  string $path
     * @param array $data
     */
    public function __construct($data = [], $path = null)
    {
        if(!empty($path)) {
            $this->setPath($path);
        }

        $this->data = $data;
    }

    /**
     * Get full file path
     *
     * @param $filename
     * @return string
     */
    private function fullFilePath($filename)
    {
        $fullFilePath = "";

        if (!empty($this->path) && !empty($filename)) {
            $fullFilePath = $this->path . DIRECTORY_SEPARATOR . $filename;
        }

        return $fullFilePath;
    }

    /**
     * @param BaseConverter $converter
     * @param $filename
     * @return mixed
     */
    private function convert(BaseConverter $converter, $filename)
    {
        if (!empty($filename) && empty($this->getPath())) {
            throw new InvalidArgumentException("You must initialize the converter with a valid path");
        }

        return $converter->convert($this->data)->write($this->fullFilePath($filename));
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    private function isDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException("The path supplied is not a directory");
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        $this->isDirectory($path);

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Convert Array to JSON
     * @param $filename
     * @return mixed
     */
    public function toJson($filename = "")
    {
        return $this->convert(new JsonConverter(), $filename);

    }

    /**
     * Convert Array to XML
     * @param $filename
     * @return mixed
     */
    public function toXml($filename = "")
    {
        return $this->convert(new XmlConverter(), $filename);

    }

}