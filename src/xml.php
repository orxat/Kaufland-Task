<?php


require 'Config.php';
require 'Database.php';
require 'Logger.php';
require 'Processor.php';

$options = getopt("", ["dbtype:", "fltype:", "path:"]); // Added options for the terminal
$dbType = $options['dbtype'] ?? 'sqlite';
$fileType = $options['fltype'] ?? 'xml';
$filePath = $options['path'] ?? 'feed.xml';
$config = new Config(__DIR__ . '/../config.ini');
$config->set('database', 'type', $dbType);
$config->set('file', 'type', $fileType);
$config->set('file', 'path', $filePath);
$logger = new Logger(__DIR__ . '/../logs/error.log');

$db = new Database($config);
$processor = new Processor($config, $db, $logger);

    $processor->process();


