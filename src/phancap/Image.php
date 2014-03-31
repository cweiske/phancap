<?php
namespace phancap;

class Image
{
    protected $config;
    public $name;


    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getPath()
    {
        return $this->config->cacheDir . $this->name;
    }

    public function getUrl()
    {
        return $this->config->cacheDirUrl . $this->name;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
    }
}
?>