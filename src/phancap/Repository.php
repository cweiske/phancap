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
        if (!$this->isAvailable($img)) {
            $this->render($img, $options);
        }
        return $img;
    }

    public function getFilename(Options $options)
    {
        return parse_url($options->values['url'], PHP_URL_HOST)
            . '-' . md5(\serialize($options->values))
            . '.' . $options->values['sformat'];
    }

    public function isAvailable(Image $img)
    {
        $path = $img->getPath();
        if (!file_exists($path)) {
            return false;
        }
        //FIXME: add cache lifetime check

        return true;
    }

    protected function render(Image $img, Options $options)
    {
        $adapter = new Adapter_Cutycapt();
        $adapter->render($img, $options);
    }
}
?>
