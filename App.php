<?php

require __DIR__.'/vendor/autoload.php';

use App\Commands\ConvertFileCommand;
use Symfony\Component\Console\Application;

$app = new Application("XML Converter");
$app->add(new ConvertFileCommand());

$app->run();