<?php
namespace phancap;

class Repository
{
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    public function getImage(Options $options)
    {
        $name = $this->getFilename($options);
        $img = new Image($name);
        $img->setConfig($this->config);
        if (!$this->isAvailable($img, $options)) {
            $this->render($img, $options);
        }
        return $img;
    }

    public function getFilename(Options $options)
    {
        $optValues = $options->values;
        unset($optValues['smaxage']);
        unset($optValues['atimestamp']);
        unset($optValues['asignature']);
        unset($optValues['atoken']);

        return parse_url($optValues['url'], PHP_URL_HOST)
            . '-' . md5(\serialize($optValues))
            . '.' . $optValues['sformat'];
    }

    /**
     * Check if the image is available locally.
     *
     * @return boolean True if we have it and it's within the cache lifetime,
     *                 false if the cache expired or the screenshot does not
     *                 exist.
     */
    public function isAvailable(Image $img, Options $options)
    {
        $path = $img->getPath();
        if (!file_exists($path)) {
            return false;
        }

        if (filemtime($path) < time() - $options->values['smaxage']) {
            return false;
        }

        return true;
    }

    protected function render(Image $img, Options $options)
    {
        $adapter = new Adapter_Cutycapt();
        $adapter->render($img, $options);
    }
}
?>
