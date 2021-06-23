<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Converter;

use DOMDocument;
use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;
use SimpleXMLElement;

class XmlConverter implements ConverterHandlerInterface
{
    public const FILE_EXTENSION = 'xml';
    public const DEFAULT_ROOT_ELEMENT = 'data';
    public const DEFAULT_RECORD_ELEMENT = 'item';

    /**
     * XML node root element.
     */
    protected $recordElement;

    /**
     * The converted data.
     *
     * @var string
     */
    protected $data;

    /**
     * XmlConverter constructor.
     */
    public function __construct(string $recordElement = self::DEFAULT_RECORD_ELEMENT)
    {
        if (!empty($recordElement)) {
            $this->recordElement = $recordElement;
        }

        $this->data = new SimpleXMLElement('<?xml version="1.0"?><data value=""></data>');
    }

    public function getExtension(): string
    {
        return self::FILE_EXTENSION;
    }

    protected function toXml(array $data, SimpleXMLElement $xmlData): void
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = $this->recordElement;
            }
            if (is_array($value)) {
                $subNode = $xmlData->addChild($key);
                $this->toXml($value, $subNode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    public function convert(array $data): ConverterHandlerInterface
    {
        $this->toXml($data, $this->data);

        return $this;
    }

    public function write(string $filename): bool
    {
        $dom = new DOMDocument('1.0');

        $dom->preserveWhiteSpace = false;

        $dom->formatOutput = true;

        $domXml = dom_import_simplexml($this->data);

        $domXml = $dom->importNode($domXml, true);

        $dom->appendChild($domXml);

        return (bool) $dom->save($filename);
    }
}
