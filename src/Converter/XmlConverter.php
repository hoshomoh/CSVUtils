<?php

namespace Oshomo\CsvUtils\Converter;


use DOMDocument;
use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;
use SimpleXMLElement;

class XmlConverter implements ConverterHandlerInterface
{

    const FILE_EXTENSION = "xml";
    const DEFAULT_ROOT_ELEMENT = "data";
    const DEFAULT_RECORD_ELEMENT = "item";

    /**
     * XML node root element
     */
    protected $recordElement;

    /**
     * The converted data
     *
     * @var string
     */
    protected $data;


    /**
     * XmlConverter constructor.
     *
     * @param string $recordElement
     */
    public function __construct($recordElement = self::DEFAULT_RECORD_ELEMENT)
    {
        if (!empty($recordElement)) {
            $this->recordElement = $recordElement;
        }

        $this->data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return self::FILE_EXTENSION;
    }

    /**
     * @param $data
     * @param $xmlData
     */
    protected function toXml($data, $xmlData) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = $this->recordElement;
            }
            if( is_array($value) ) {
                $subNode = $xmlData->addChild($key);
                $this->toXml($value, $subNode);
            } else {
                $xmlData->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    /**
     * @param $data
     * @return $this|mixed
     */
    public function convert($data)
    {
        $this->toXml($data, $this->data);

        return $this;
    }

    /**
     * @param $filename
     * @return bool
     */
    public function write($filename)
    {
        $dom = new DOMDocument('1.0');

        $dom->preserveWhiteSpace = false;

        $dom->formatOutput = true;

        $domXml = dom_import_simplexml($this->data);

        $domXml = $dom->importNode($domXml, true);

        $dom->appendChild($domXml);

        return ($dom->save($filename)) ? true : false;
    }
}
