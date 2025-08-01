<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpTestTask\DataImporter;

$importer = new DataImporter();
$importer->runImport();