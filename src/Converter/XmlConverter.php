<?php
/**
 * Created by PhpStorm.
 * User: oshomo.oforomeh
 * Date: 30/01/2017
 * Time: 5:33 PM
 */

namespace Oshomo\CsvUtils\Converter;

use DOMDocument;
use SimpleXMLElement;

class XmlConverter extends BaseConverter
{

    /**
     * The converted data
     *
     * @var string
     */
    private $data;

    public function __construct()
    {
        $this->data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
    }

    private function toXml($data, $xml_data) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $sub_node = $xml_data->addChild($key);
                $this->toXml($value, $sub_node);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    public function convert($data)
    {
        $this->toXml($data, $this->data);
        return $this;
    }

    public function write($filename)
    {
        if (empty($filename)) {
            return $this->data->asXML();
        } else {
            // Use DOMDocument to beautify the SimpleXML output
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom_xml = dom_import_simplexml($this->data);
            $dom_xml = $dom->importNode($dom_xml, true);
            $dom_xml = $dom->appendChild($dom_xml);

            if (!$dom->save($filename)) {
                return "Data to XML conversion not successful.";
            } else {
                return "Data to XML conversion successful. Check {$filename} for your file.";
            }
        }

    }
}