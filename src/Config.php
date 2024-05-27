<?php

class Config
{
    private $config;

    public function __construct($file)
    {
        $this->config = parse_ini_file($file, true);
    }

    public function get($section, $key)
    {
        return $this->config[$section][$key];
    }
    public function set($section, $key, $value)
    {
        $this->config[$section][$key] = $value;
    }
}
