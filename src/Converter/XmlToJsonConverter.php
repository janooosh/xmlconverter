<?php

namespace App\Converter;

use Exception;

class XmlToJsonConverter extends XmlConverter
{
    private String $extension = ".json";

    public function getExtension()
    {
        return $this->extension;
    }

    public function convert():array
    {
        $errorMessages = [];

        $data = $this->getData();
        try {
            $json = json_encode($data);
            $this->setConvertedData($json);
        }
        catch(Exception $e) {
            $errorMessages[] = $e->getMessage();
        }

        return $errorMessages;
    }

    public function store():bool
    {
        $baseFileName = $this->getBaseFileName();
        $fileName = $baseFileName.$this->getExtension();
        file_put_contents($fileName,$this->getConvertedData());
        return true;
    }
}