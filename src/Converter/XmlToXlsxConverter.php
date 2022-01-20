<?php

namespace App\Converter;

use Exception;
use SimpleXMLElement;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XmlToXlsxConverter extends XmlConverter
{
    protected String $extension = ".xlsx";
    
    protected Spreadsheet $spreadsheet;

    public function getExtension()
    {
        return $this->extension;
    }
    protected function setSpreadsheet(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }
    protected function getSpreadsheet()
    {
        return $this->spreadsheet;
    }

    public function convert():array
    {
        $messages = [];

        //Erzeuge Header
        $this->constructHeader();
        $headers = $this->getHeaders();

        //Erzeuge Body
        $bodyRows = $this->constructBody();

        //Erzeuge das Spreadsheet
        $spreadsheet = new Spreadsheet();
        $this->setSpreadsheet($spreadsheet);
        
        /**
         * Iteratoren
         * x und y Iteratoren stehen für Zellenbasierte Iteratoren in einem Spreadsheet.
         * z.B. steht $x=2 und $y=3 für die Zelle "B3"
         */

        //Base Iteratoren ("starte bei Zelle A1")
        $initX=1;
        $initY=1;

        //Fülle Kopfzeile
        $headerX = $initX;
        $headerY = $initY;
        while($headerX <= count($headers)) 
        {
            $cellSetResult = $this->setSpreadsheetCell($headerX,$headerY,$headers[$headerX-1]);
            if(!$cellSetResult) return ["EmptyCellSetResults"];
            $headerX++;
        }     

        //Fülle alle weiteren Zeilen aus (Body)
        $bodyY = $initY+1;
        foreach($bodyRows as $rowItems)
        {
            $rowX = $initX;
            foreach($rowItems as $r)
            {
                $this->setSpreadsheetCell($rowX,$bodyY,$r);
                $rowX++;
            }
            $bodyY++;
        }

        return $messages;
    }

    public function store():bool
    {
        $spreadsheet = $this->getSpreadsheet();
        $writer = new Xlsx($spreadsheet);
        $baseFileName = $this->getBaseFileName();
        $filePath = $baseFileName.$this->getExtension();
        try {
            $writer->save($filePath);
        }
        catch(Exception $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Wandle ein numerischen Wert in einen Buchstaben oder eine Buchstabenkette
     * des Alphabets um.
     */
    public function getCharacterByNumber(Int $number):mixed
    {
        if($number < 1 || $number > 26)
        {
            return null;
        }
        $range = range("A","Z");

        return $range[$number-1];
    }

    private function setSpreadsheetCell(Int $indexX,Int $indexY,$content):bool
    {
        $indexX = $this->getCharacterByNumber($indexX);
        if(is_null($indexX))
        {
            return false;
        }

        $this->spreadsheet->getActiveSheet()->setCellValue($indexX.$indexY,$content);

        return true;
    }
    
}