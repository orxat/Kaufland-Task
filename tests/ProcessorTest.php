<?php

require_once 'vendor/autoload.php';
require './src/Config.php';
require './src/Database.php';
require './src/Logger.php';
require './src/Processor.php';

use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
   
    public function testProcess()
    {
        $logger = $this->createMock(Logger::class);
        $db = $this->createMock(Database::class);
        $config = $this->createMock(Config::class);

        $options = getopt("", ["dbtype:", "fltype:", "path:"]);
        $dbType = $options['dbtype'] ?? 'sqlite';
        $fileType = $options['fltype'] ?? 'xml';
        $filePath = $options['path'] ?? 'feed.xml';
        $config->method('get')
            ->willreturnCallback(function ($section, $key) use ($dbType, $fileType, $filePath) {
                if ($section === 'database' && $key === 'type') {
                    return $dbType;
                } elseif ($section === 'file' && $key === 'type') {
                    return $fileType;
                } elseif ($section === 'file' && $key === 'path') {
                    return $filePath;
                }
            });

        $processor = new Processor($config, $db, $logger);

        // Expecting the 'insertItem' method of the database mock to be called at least once
        $db->expects($this->atLeastOnce())
            ->method('insertItem');

        
        $processor->process();
    }
    public function testProcessWithWrongXMLFile()
    {
        // Creating mock objects
        $logger = $this->createMock(Logger::class);
        $db = $this->createMock(Database::class);
        $config = $this->createMock(Config::class);

        
        $options = getopt("", ["dbtype:", "fltype:", "path:"]);
        $dbType = $options['dbtype'] ?? 'sqlite';
        $fileType = $options['fltype'] ?? 'xml';
        $filePath = $options['path'] ?? 'xfeed.xml';

        
        $config->method('get')
            ->willreturnCallback(function ($section, $key) use ($dbType, $fileType, $filePath) {
                if ($section === 'database' && $key === 'type') {
                    return $dbType;
                } elseif ($section === 'file' && $key === 'type') {
                    return $fileType;
                } elseif ($section === 'file' && $key === 'path') {
                    return $filePath;
                }
            });

        // Creating an instance of Processor with mock objects
        $processor = new Processor($config, $db, $logger);


        // Expects that the logger logs an error message about the  file not found
        $logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains($filePath . " not found: "));

       
        $processor->process();
    }
    public function testProcessWithWrongType()
    {
        // Create mock objects
        $logger = $this->createMock(Logger::class);
        $db = $this->createMock(Database::class);
        $config = $this->createMock(Config::class);

       
        $options = getopt("", ["dbtype:", "fltype:", "path:"]);
        $dbType = $options['dbtype'] ?? 'sqlite';
        $fileType = $options['fltype'] ?? 'jpg';
        $filePath = $options['path'] ?? 'feed.xml';

        
        $config->method('get')
            ->willreturnCallback(function ($section, $key) use ($dbType, $fileType, $filePath) {
                if ($section === 'database' && $key === 'type') {
                    return $dbType;
                } elseif ($section === 'file' && $key === 'type') {
                    return $fileType;
                } elseif ($section === 'file' && $key === 'path') {
                    return $filePath;
                }
            });

       
        $processor = new Processor($config, $db, $logger);


        // Expects that the logger logs an error message about the typefile not being correct
        $logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains($fileType . " not correct: "));

      
        $processor->process();
    }
}
