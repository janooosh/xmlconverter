<?php

namespace App\Commands;


use App\Converter\XmlToCsvConverter;
use App\Converter\XmlToJsonConverter;
use App\Converter\XmlToXlsxConverter;
use App\FileValidator\XmlValidator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertFileCommand extends Command
{

    private Array $allowedConversionFormats = ["json","csv","xlsx"];
    private OutputInterface $output;

    private SymfonyStyle $style;

    protected function getOutputInterface()
    {
        return $this->output;
    }
    protected function getAllowedConversionFormats()
    {
        return $this->allowedConversionFormats;
    }
    protected function setOutputInterface($output)
    {
        $this->output = $output;
    }
    private function setStyle(InputInterface $input,OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input,$output);
    }
    protected function getStyle()
    {
        return $this->style;
    }

    protected function configure()
    {
        $this->setName("convertFile")
        ->setDescription("Converts an XML File")
        ->setHelp("Command to convert an XML File")
        ->addArgument('filepath',InputArgument::REQUIRED,"Path to the file that needs to be converted")
        ->addArgument('format',InputArgument::REQUIRED,"Accepts json,csv or xlsx");
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        $this->setStyle($input,$output);
        $this->getStyle()->title("Starte Konvertierung");

        //Get Input
        $filepath = $input->getArgument('filepath');
        $format = $input->getArgument('format');

        //Validate Format
        if(!in_array($format,$this->getAllowedConversionFormats())) {
            return $this->failWithMessages("Das angefragte Format wird nicht akzeptiert.");
        }
        $this->printTextMessage("Angefragtes Format ok.");

        //Validate File
        $fileValidator = new XmlValidator($filepath);
        $validationMessages = $fileValidator->validate();
        if(!empty($validationMessages)) {
            return $this->failWithMessages($validationMessages);
        }
        $this->printTextMessage("Dateivalidierung ok.");

        //Init Converter
        $file = $fileValidator->getFile();
        $pathInfo = $fileValidator->getFilePathInfo();
        $basePathName = $pathInfo['dirname']."/".$pathInfo['filename'];

        switch($format) {
            case 'json':
                $converter = new XmlToJsonConverter($file,$basePathName);
                break;
            case 'csv':
                $converter = new XmlToCsvConverter($file,$basePathName);
                break;
            case 'xlsx':
                $converter = new XmlToXlsxConverter($file,$basePathName);
                break;
            default:
                return $this->failWithMessages("Kein gültiges Format.");
        }
        
        //Convert
        $conversionMessages = $converter->convert();
        if(!empty($conversionMessages)) {
            return $this->failWithMessages($conversionMessages);
        }
        $this->printTextMessage("Konvertierung ok.");

        //Store
        $storeResult = $converter->store();
        if(!$storeResult) {
            return $this->failWithMessages("Die konvertierte Datei konnte nicht gespeichert werden.");
        }

        $this->printSuccessMessage("Die Datei wurde erfolgreich gespeichert.");
        return Command::SUCCESS;        
    }

    protected function failWithMessages(Mixed $m):int
    {
        $type = gettype($m);

        switch($type) {
            case 'string':
                $this->printErrorMessage($m);
                break;
            case 'array':
                $this->printErrorMessages($m);
                break;
        }
        return COMMAND::FAILURE;
    }

    protected function printErrorMessage($message):void
    {
        $this->getStyle()->error($message);
    }

    protected function printSuccessMessage($message):void
    {
        $this->getStyle()->success($message);
    }
    protected function printTextMessage($message):void
    {
        $this->getStyle()->text($message);
    }

    protected function printErrorMessages(Array $errorMessages):void
    {
        foreach($errorMessages as $errorMessage) {
            $this->printErrorMessage($errorMessage);
        }
    }
}