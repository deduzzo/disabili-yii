<?php

namespace app\helpers;


use Exception;

class SepaParser
{
    private $xmlFile;
    private $xmlObject;

    /**
     * @throws Exception
     */
    public function __construct($filePath)
    {
        if (file_exists($filePath)) {
            $this->xmlFile = $filePath;
            $this->loadXml();
        } else {
            throw new Exception("File not found: " . $filePath);
        }
    }

    /**
     * @throws Exception
     */
    private function loadXml()
    {
        $this->xmlObject = simplexml_load_file($this->xmlFile);
        if (!$this->xmlObject) {
            throw new Exception("Unable to load XML file.");
        }
    }

    public function parseToArray()
    {
        return $this->convertXmlToArray($this->xmlObject);
    }

    private function convertXmlToArray($xml, &$result = [])
    {
        foreach ((array)$xml as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = [];
                $this->convertXmlToArray($value, $result[$key]);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}