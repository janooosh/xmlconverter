<?php

namespace App\Converter;

class XmlToCsvConverter extends XmlConverter
{
    protected String $extension = ".csv";

    private String $temporaryFilePath;

    public function getExtension()
    {
        return $this->extension;
    }

    protected function setTemporaryFilePath($temporaryFilePath)
    {
        $this->temporaryFilePath = $temporaryFilePath;
    }
    protected function getTemporaryFilePath()
    {
        return $this->temporaryFilePath;
    }


    public function convert():array
    {
        $messages = [];

        //Erstelle eine temporäre Datei (noch nicht im Output Verzeichnis)
        $temporaryFilePath = sys_get_temp_dir().uniqid();
        while(file_exists($temporaryFilePath)) {
            $temporaryFilePath = sys_get_temp_dir().uniqid();
        }
        $file = fopen($temporaryFilePath,'w');

        $this->constructHeader();
        $headers = $this->getHeaders();
        fputcsv($file,$headers);

        $bodyRows = $this->constructBody();
        foreach($bodyRows as $bodyRow) {
            fputcsv($file,$bodyRow);
        }

        //Speichere den Pfad zur temporären Datei
        $this->setTemporaryFilePath($temporaryFilePath);
        
        return $messages;
    }

    public function store():bool
    {
        //Speichere die temporäre Datei im "richtigen" Verzeichnis ab.
        $baseFileName = $this->getBaseFileName();
        $fileName = $baseFileName.$this->extension;

        rename($this->getTemporaryFilePath(),$fileName);        
        return true;
    }

}