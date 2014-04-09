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

    /**
     * @return integer Unix timestamp
     */
    public function getExpiryDate(Options $options)
    {
        $mtime = filemtime($this->getPath());

        return $mtime + $options->values['smaxage'];
    }

    public function getMimeType()
    {
        $ext = substr($this->name, -4);
        if ($ext == '.jpg') {
            return 'image/jpeg';
        } else if ($ext == '.png') {
            return 'image/png';
        } else  if ($ext == '.png') {
            return 'application/pdf';
        }
        return 'application/octet-stream';
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