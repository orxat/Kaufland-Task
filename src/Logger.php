<?php

class Logger {
    private $logFile;

    public function __construct($logFile) {
        $this->logFile = $logFile;
    }

    public function log($message) {
        file_put_contents($this->logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
    }
}
