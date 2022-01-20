<?php

namespace App\Converter;

use App\GenericConverter;
use SimpleXMLElement;

abstract class XmlConverter implements GenericConverter
{   
    protected SimpleXMLElement $data;
    protected Mixed $convertedData; 
    protected String $baseFileName; //Dateiname ohne Erweiterung
    
    protected Array $headers = [];
    private String $headerSeparator = ">";

    abstract public function convert():array;
    abstract public function store():bool;

    public function __construct(SimpleXMLElement $data,String $baseFileName)
    {
        $this->data = $data;
        $this->baseFileName = $baseFileName;
    }

    public function getData()
    {
        return $this->data;
    }
    public function getBaseFileName()
    {
        return $this->baseFileName;
    }
    public function getConvertedData()
    {
        return $this->convertedData;
    }
    protected function getHeaderSeparator()
    {
        return $this->headerSeparator;
    }
    protected function getHeaders()
    {
        return $this->headers;
    }
    protected function setConvertedData($convertedData)
    {
        $this->convertedData = $convertedData;
    }
    private function addElementToHeader(String $headerItemName)
    {
        $this->headers[] = $headerItemName;
    }


    /**
     * constructHeader stellt ein Array aus uniquen TagNames zusammen, 
     * welcher die hierarchische Struktur der XML Tags berücksichtigt (getrennt durch Separator).
     * Es setzt voraus, dass der Separator String nicht in den XML Tags enthalten ist.
     * Beispiel: [parent,parent>childA,parent>childB,parent>childB>grandchildA,...]
     */
    protected function constructHeader(SimpleXmlElement $xmlRow=null,String $separator=null,String $prefix="")
    {
        if(is_null($xmlRow)) {
            $xmlRow = $this->getData();
        }
        if(is_null($separator)) {
            $separator = $this->getHeaderSeparator();
        }

        foreach($xmlRow->children() as $row) {
            $parentName = $prefix.$row->getName();
            $subfields = $row->children();
            if(count($subfields) > 0) {
                $this->constructHeader($row,$separator,$parentName.$separator);               
            }
            else {
                if(!in_array($parentName,$this->getHeaders())) {
                    $this->addElementToHeader($parentName);
                }
            }
        }
    }

    /**
     * Diese Funktion erstellt ein mehrdimensionales Array,
     * unter Berücksichtigung der Struktur der XML Datei und den identifierzen Headern.
     */
    protected function constructBody():array
    {
        $body = array();
        $rows = $this->getData();
        foreach($rows as $row) {
            $rowValues = $this->constructBodyRow($row);
            $body[] = $rowValues;
        }
        return $body;
    }

    /**
     * Diese Funktion erstellt ein Array aus Werten, die einer "Zeile" entsprechen.
     * Das Array hat die Länge des Header Arrays. 
     */
    private function constructBodyRow(SimpleXmlElement $xmlRow):array
    {
        $rowValues = array();

        $headers = $this->getHeaders();
        foreach($headers as $header) {
            $headerLevels = explode($this->getHeaderSeparator(),$header);
            $rowValues[] = $this->constructBodyRowValue($xmlRow,$headerLevels);
        }

        return $rowValues;
    }

    /**
     * Für ein gegebenen Header wird hier der dazugehörige Wert einer Zeile der XML datei ermittelt.
     * Zurückgegeben wird entweder der Wert der XML Datei oder null.
     * Der Parametre headerLevels erwartet ein eindimensionales Array der Header.
     * Beispiel: <a><b><c></c></b></a> muss als ["a","b","c"] übergeben werden. 
     */
    private function constructBodyRowValue(SimpleXmlElement $xmlRow,Array $headerLevels,Int $iterator=0)
    {
        $headerName = $headerLevels[$iterator];
        
        if($headerName !== $xmlRow->getName()) {
            return null;
        }

        else {
            
            if(!$xmlRow->children() && $iterator===count($headerLevels)-1) {
                return $xmlRow;
            }

            elseif(array_key_exists($iterator+1,$headerLevels)) {
                $headerToSearchFor = $headerLevels[$iterator+1];
                foreach($xmlRow->children() as $childRow) {
                    if($childRow->getName() == $headerToSearchFor) {
                        return $this->constructBodyRowValue($childRow,$headerLevels,$iterator+1);
                    }
                }
            }
            return null;
        }
    }

}