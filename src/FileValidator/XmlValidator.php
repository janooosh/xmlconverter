<?php

namespace App\FileValidator;

use SimpleXMLElement;

class XmlValidator extends FileValidator
{

    private SimpleXMLElement $xmlElement;

    //Hier könnte ein Array an weiteren Validierungsregeln definiert werden, z.B. Dateigröße/MIME-Type...

    public function validate():array
    {

        $loadFileResult = $this->loadSimpleXmlFile();
        if(!empty($loadFileResult)) {
            return $loadFileResult;
        }

        return [];
    }

    public function getFile():mixed
    {
        return $this->xmlElement;
    }
    protected function setFile($xmlElement):void
    {
        $this->xmlElement = $xmlElement;
        $this->setFilePathInfo();
    }

    /**
     * Öffnet die XML Datei und konvertiert sie zu einem SimpleXMLElement.
     * @return Array Fehlermeldungen
     */
    private function loadSimpleXmlFile():array
    {   
        $messages = [];
        
        /**
         * simplexml_load_file kann false oder eine E_WARNING zurückgeben.
         * Mit libxml_use_internal_errors verhindern wir, dass es zu ungewollten Rückmeldungen kommt.
         * https://www.php.net/manual/en/function.simplexml-load-file.php
         * In der Zukunft könnte man  z.B. mit einem Custom Error Handler, auch die E_WARNING auswerten 
         * und dem Rückgabe Array eine entsprechende Meldung hinzufügen.
         */
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($this->getFilePath());
        libxml_clear_errors();

        if($xml === false) {
            $messages[] = "Die Datei kann nicht im XML Format geladen werden.";
        }
        
        else {
            $this->setFile($xml);
        }

        return $messages;
    }
} 