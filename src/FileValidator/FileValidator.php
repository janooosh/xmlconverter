<?php

namespace App\FileValidator;

use App\GenericValidator;

abstract class FileValidator implements GenericValidator {
    
    private String $filePath;
    private Array $filePathInfo;

    public function __construct(String $filePath) {
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }
    public function getFilePathInfo()
    {
        return $this->filePathInfo;
    }
    protected function setFilePathInfo()
    {
        $this->filePathInfo = pathinfo($this->getFilePath());
    }
    
    abstract public function validate():array;
    /**
     * Die eigentliche Datei wird nicht in dieser abstrakten Klasse gespeichert.
     * Je nach Dateityp gibt es verschiedene Wege, mit der Datei umzugehen.
     * Child-Klassen müssen jedoch folgende Methoden implementieren, um die Datei zu setzen und zu lesen,
     * damit andere Klassen mit der validierten Datei arbeiten können.
     */
    abstract public function getFile():mixed;
    abstract protected function setFile($fileData):void;

}